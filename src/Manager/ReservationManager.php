<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Order;
use App\Entity\Part;
use App\Entity\Reservation;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
final class ReservationManager
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var PartManager
     */
    private $partManager;

    public function __construct(RegistryInterface $registry, PartManager $partManager)
    {
        $this->registry = $registry;
        $this->partManager = $partManager;
    }

    public function reserve(Part $part, int $quantity, Order $order): void
    {
        if (0 >= $quantity) {
            throw new ReservationException('Количество резервируемого товара должно быть положительным.');
        }

        $reservable = $this->reservable($part);
        if ($reservable < $quantity) {
            throw new ReservationException(
                \sprintf('Невозможно зарезервировать "%s" единиц товара, доступно "%s"', $quantity / 100, $reservable)
            );
        }

        $em = $this->registry->getEntityManager();

        $em->persist(new Reservation($part, $quantity, $order));
        $em->flush();
    }

    public function deReserve(Part $part, int $quantity, Order $order): void
    {
        if (0 >= $quantity) {
            throw new ReservationException('Количество снимаемого с резерва товара должно быть положительным.');
        }

        $reserved = $this->reserved($part, $order);
        if ($reserved < $quantity) {
            throw new ReservationException(
                \sprintf('Невозможно снять с резервации "%s" единиц товара, в резерве "%s"', $quantity / 100, $reserved)
            );
        }

        $em = $this->registry->getEntityManager();

        $em->persist(new Reservation($part, 0 - $quantity, $order));
        $em->flush();
    }

    public function reservable(Part $part): int
    {
        return $this->partManager->inStock($part) - $this->reserved($part);
    }

    public function reserved(Part $part, Order $order = null): int
    {
        $em = $this->registry->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->select('SUM(entity.quantity)')
            ->from(Reservation::class, 'entity')
            ->groupBy('entity.part')
            ->where('entity.part = :part')
            ->setParameter('part', $part);

        if (null !== $order) {
            $qb->andWhere('entity.order = :order')
                ->setParameter('order', $order);
        }

        try {
            return (int) $qb
                ->getQuery()
                ->getSingleResult(Query::HYDRATE_SINGLE_SCALAR);
        } catch (NoResultException $e) {
            return 0;
        }
    }

    /**
     * @return Order[]
     */
    public function orders(Part $part): array
    {
        $em = $this->registry->getEntityManager();

        return $em->createQueryBuilder()
            ->select('entity')
            ->from(Order::class, 'entity')
            ->join(Reservation::class, 'reservation', Join::WITH, 'reservation.order = entity')
            ->where('reservation.part = :part')
            ->orderBy('entity.id', 'DESC')
            ->setParameters([
                'part' => $part,
            ])
            ->getQuery()
            ->getResult();
    }
}

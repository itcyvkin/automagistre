<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Employee;
use App\Entity\Operand;
use App\Entity\Person;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
final class WorkerType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $em = $this->em;

        $resolver->setDefaults([
            'label' => false,
            'placeholder' => 'Выберите исполнителя',
            'choice_loader' => new CallbackChoiceLoader(function () use ($em) {
                $employees = $em->createQueryBuilder()
                    ->select('person')
                    ->from(Person::class, 'person')
                    ->join(Employee::class, 'employee', Join::WITH, 'person = employee.person')
                    ->where('employee.firedAt IS NULL')
                    ->getQuery()
                    ->getResult();

                $contractors = $em->createQueryBuilder()
                    ->select('entity')
                    ->from(Operand::class, 'entity')
                    ->where('entity.contractor = :is_contractor')
                    ->setParameter('is_contractor', true)
                    ->getQuery()
                    ->getResult();

                return \array_merge($employees, $contractors);
            }),
            'choice_label' => function (Operand $operand) {
                return (string) $operand;
            },
            'choice_value' => 'id',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @UniqueEntity(fields={"telephone"}, message="Клиент с таким телефоном уже существует")
 */
class Person extends Operand
{
    /**
     * @var string
     *
     * @ORM\Column(length=32, nullable=true)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $lastname;

    /**
     * @var string
     *
     * @Assert\Email
     *
     * @ORM\Column(nullable=true)
     */
    private $email;

    /**
     * @var PhoneNumber
     *
     * @ORM\Column(type="phone_number", nullable=true)
     */
    private $telephone;

    /**
     * @var PhoneNumber
     *
     * @ORM\Column(type="phone_number", nullable=true)
     */
    private $officePhone;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $spriteId;

    public function __toString(): string
    {
        $string = $this->getFullName();
        $email = $this->getEmail();

        if (null !== $email) {
            $string .= \sprintf(' (%s)', $email);
        }

        return $string;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getTelephone(): ?PhoneNumber
    {
        return $this->telephone;
    }

    public function setTelephone(?PhoneNumber $telephone): void
    {
        $this->telephone = $telephone;
    }

    public function getOfficePhone(): ?PhoneNumber
    {
        return $this->officePhone;
    }

    public function setOfficePhone(?PhoneNumber $officePhone): void
    {
        $this->officePhone = $officePhone;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getFullName(): string
    {
        return \sprintf('%s %s', $this->lastname, $this->firstname);
    }
}

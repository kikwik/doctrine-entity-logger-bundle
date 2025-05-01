<?php

namespace Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;


    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $name = '';

    public function __toString(): string
    {
        return (string)$this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): User
    {
        $this->name = $name;
        return $this;
    }
}
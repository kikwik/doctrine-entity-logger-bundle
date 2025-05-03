<?php

namespace Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Kikwik\DoctrineEntityLoggerBundle\Attributes\LoggableEntity;

#[ORM\Entity()]
#[LoggableEntity]
class Partner
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $name = '';

    #[ORM\OneToOne(inversedBy: 'partner', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Author $author = null;

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

    public function setName(?string $name): Partner
    {
        $this->name = $name;
        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): static
    {
        // unset the owning side of the relation if necessary
        if ($author === null && $this->author !== null) {
            $this->author->setPartner(null);
        }

        // set the owning side of the relation if necessary
        if ($author !== null && $author->getPartner() !== $this) {
            $author->setPartner($this);
        }

        $this->author = $author;

        return $this;
    }
}
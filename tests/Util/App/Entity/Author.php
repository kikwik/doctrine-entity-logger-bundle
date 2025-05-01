<?php

namespace Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Kikwik\DoctrineEntityLoggerBundle\Attributes\LoggableEntity;

#[ORM\Entity]
#[LoggableEntity]
class Author
{
    use BlameableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;


    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $name = '';

    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'author')]
    private Collection $articles;

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

    public function setName(?string $name): Author
    {
        $this->name = $name;
        return $this;
    }

    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function setArticles(Collection $articles): Author
    {
        $this->articles = $articles;
        return $this;
    }


}
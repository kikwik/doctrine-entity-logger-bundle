<?php

namespace Kikwik\DoctrineEntityLoggerBundle\Tests\Util\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $name = '';

    #[ORM\ManyToMany(targetEntity: Article::class, mappedBy: 'tags')]
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

    public function setName(?string $name): Tag
    {
        $this->name = $name;
        return $this;
    }

    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function setArticles(Collection $articles): Tag
    {
        $this->articles = $articles;
        return $this;
    }



}
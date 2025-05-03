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

    #[ORM\OneToOne(mappedBy: 'author', cascade: ['persist','remove'], orphanRemoval: true)]
    private ?Partner $partner = null;

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

    public function addArticles(Article $article): Author
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setAuthor($this);
        }
        return $this;
    }

    public function removeArticles(Article $article): Author
    {
        if ($this->articles->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getAuthor() === $this) {
                $article->setAuthor(null);
            }
        }
        return $this;
    }

    public function getPartner(): ?Partner
    {
        return $this->partner;
    }

    public function setPartner(?Partner $partner): static
    {
        $this->partner = $partner;
        return $this;
    }
}
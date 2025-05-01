<?php

namespace Kikwik\DoctrineEntityLoggerBundle\Tests\Util\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;
use Kikwik\DoctrineEntityLoggerBundle\Attributes\LoggableEntity;

#[ORM\Entity]
#[LoggableEntity]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $title = '';

    #[ORM\ManyToOne(targetEntity: Author::class)]
    private ?Author $author = null;

    #[ManyToMany(targetEntity: Tag::class)]
    private Collection $tags;

    public function __toString(): string
    {
        return (string)$this->title;
    }

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): Article
    {
        $this->author = $author;
        return $this;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): Article
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
        return $this;
    }

    public function removeTag(Tag $tag): Article
    {
        $this->tags->removeElement($tag);
        return $this;
    }

}
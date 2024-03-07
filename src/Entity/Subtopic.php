<?php

namespace App\Entity;

use App\Repository\SubtopicRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SubtopicRepository::class)]
class Subtopic
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 70)]
    #[Groups(['api_posts_read'])]
    private ?string $name = null;

    #[ORM\Column(length: 70, nullable: true)]
    #[Groups(['api_posts_read'])]
    private ?string $slug = null;

    #[ORM\ManyToMany(targetEntity: Posts::class, mappedBy: 'subtopic')]
    private Collection $posts;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection<int, Posts>
     */
    public function getPosts(): Collection
    {
        return $this->articles;
    }

    public function addPost(Posts $post): self
    {
        if (!$this->articles->contains($post)) {
            $this->articles->add($post);
            $post->addSubtopic($this);
        }

        return $this;
    }

    public function removeArticle(Posts $post): self
    {
        if ($this->articles->removeElement($post)) {
            $post->removeSubtopic($this);
        }

        return $this;
    }

}

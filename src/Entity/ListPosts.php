<?php

namespace App\Entity;

use App\Repository\ListPostsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ListPostsRepository::class)]
#[ApiResource]
class ListPosts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['api_posts_read'])]
    private ?int $id = null;

    #[ORM\Column(length: 170, nullable: true)]
    #[Groups(['api_posts_read'])]
    private ?string $title = null;

    #[ORM\ManyToOne(inversedBy: 'ListPosts')]
    private ?Posts $posts = null;

    #[ORM\Column(length: 5000, nullable: true)]
    #[Groups(['api_posts_read'])]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPosts(): ?Posts
    {
        return $this->posts;
    }

    public function setPosts(?Posts $posts): self
    {
        $this->posts = $posts;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}

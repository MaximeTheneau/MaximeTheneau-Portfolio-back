<?php

namespace App\Entity;
use App\Repository\ExperiencesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiResource;
/**
 * @ORM\Entity(repositoryClass=ExperiencesRepository::class)
 */
class Experiences
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups("api_categories_browse")
     * @ORM\Column(type="string", length=255)
     * 
     */
    private $title;



    /**
     * @ORM\Column(type="date")
     */
    private $created_at;

    /**
     * @ORM\Column(type="time")
     */
    private $updated_at;

    /**
     * @Groups("api_categories_browse")
     * @ORM\Column(type="string", length=1024)
     */
    private $contents;



    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("api_categories_browse")
     */
    private $imageSvg;

    /**
     * @Groups("api_categories_browse")
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageWebp;

    /**
     * @Groups("api_categories_browse")
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    private $contents2;

    /**
     * @Groups("api_categories_browse")
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    private $contents3;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="experiences")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Categories::class, inversedBy="experiences")
     */
    private $categories;



    public function __construct()
    {
        $this->experiences = new ArrayCollection();
        $this->user = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getContents(): ?string
    {
        return $this->contents;
    }

    public function setContents(string $contents): self
    {
        $this->contents = $contents;

        return $this;
    }


    public function getImageSvg(): ?string
    {
        return $this->imageSvg;
    }

    public function setImageSvg(?string $imageSvg): self
    {
        $this->imageSvg = $imageSvg;

        return $this;
    }

    public function getImageWebp(): ?string
    {
        return $this->imageWebp;
    }

    public function setImageWebp(?string $imageWebp): self
    {
        $this->imageWebp = $imageWebp;

        return $this;
    }

    public function getContents2(): ?string
    {
        return $this->contents2;
    }

    public function setContents2(?string $contents2): self
    {
        $this->contents2 = $contents2;

        return $this;
    }

    public function getContents3(): ?string
    {
        return $this->contents3;
    }

    public function setContents3(?string $contents3): self
    {
        $this->contents3 = $contents3;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): self
    {
        if (!$this->user->contains($user)) {
            $this->user[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->user->removeElement($user);

        return $this;
    }

    public function getCategories(): ?Categories
    {
        return $this->categories;
    }

    public function setCategories(?Categories $categories): self
    {
        $this->categories = $categories;

        return $this;
    }

    

}

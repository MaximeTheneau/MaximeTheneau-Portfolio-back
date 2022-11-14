<?php

namespace App\Entity;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CategoriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
/**
 * @ORM\Entity(repositoryClass=CategoriesRepository::class)
 */
class Categories
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("api_categories_browse")
     */
    private $id;


    /**
     * @ORM\Column(type="string", length=128)
     * @Groups("api_categories_browse")
     */
    private $idTitle;

    /**
     * 
     * @ORM\Column(type="string", length=255)
     * @Groups("api_categories_browse")
     */
    private $imgWebp;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("api_categories_browse")
     */
    private $imgSvg;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $updated_at;


    /**
     * @Groups("api_categories_browse")
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="categories")
     */
    private $user;

    /**
     * @Groups("api_categories_browse")
     * @ORM\OneToMany(targetEntity=Experiences::class, mappedBy="categories")
     */
    private $experiences;

    /**
     * @Groups("api_categories_browse")
     * @ORM\OneToMany(targetEntity=Contact::class, mappedBy="categories")
     */
    private $contacts;

    /**
     * @Groups("api_categories_browse")
     * @ORM\OneToMany(targetEntity=About::class, mappedBy="idCategories")
     */
    private $abouts;


    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->experiences = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->user = new ArrayCollection();
        $this->abouts = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdTitle(): ?string
    {
        return $this->idTitle;
    }

    public function setIdTitle(string $idTitle): self
    {
        $this->idTitle = $idTitle;

        return $this;
    }

    public function getImgWebp(): ?string
    {
        return $this->imgWebp;
    }

    public function setImgWebp(string $imgWebp): self
    {
        $this->imgWebp = $imgWebp;

        return $this;
    }

    public function getImgSvg(): ?string
    {
        return $this->imgSvg;
    }

    public function setImgSvg(string $imgSvg): self
    {
        $this->imgSvg = $imgSvg;

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

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection<int, Experiences>
     */
    public function getExperiences(): Collection
    {
        return $this->experiences;
    }

    public function addExperience(Experiences $experience): self
    {
        if (!$this->experiences->contains($experience)) {
            $this->experiences[] = $experience;
            $experience->setCategoriesContact($this);
        }

        return $this;
    }

    public function removeExperience(Experiences $experience): self
    {
        if ($this->experiences->removeElement($experience)) {
            // set the owning side to null (unless already changed)
            if ($experience->getCategoriesContact() === $this) {
                $experience->setCategoriesContact(null);
            }
        }

        return $this;
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

    /**
     * @return Collection<int, Contact>
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts[] = $contact;
            $contact->setCategories($this);
        }

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        if ($this->contacts->removeElement($contact)) {
            // set the owning side to null (unless already changed)
            if ($contact->getCategories() === $this) {
                $contact->setCategories(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, About>
     */
    public function getAbouts(): Collection
    {
        return $this->abouts;
    }

    public function addAbout(About $about): self
    {
        if (!$this->abouts->contains($about)) {
            $this->abouts[] = $about;
            $about->setIdCategories($this);
        }

        return $this;
    }

    public function removeAbout(About $about): self
    {
        if ($this->abouts->removeElement($about)) {
            // set the owning side to null (unless already changed)
            if ($about->getIdCategories() === $this) {
                $about->setIdCategories(null);
            }
        }

        return $this;
    }
   

}

<?php



namespace App\Entity;
use App\Repository\AboutRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiResource;
/**
 * @ORM\Entity(repositoryClass=AboutRepository::class)
 */
class About
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
     */
    private $cv;

    /**
     * @Groups("api_categories_browse")
     * @ORM\Column(type="string", length=255)
     */
    private $imgWebp;

    /**
     * @Groups("api_categories_browse")
     * @ORM\Column(type="string", length=255)
     */
    private $content;

    /**
     * @Groups("api_categories_browse")
     * @ORM\Column(type="string", length=255)
     */
    private $contents2;

    /**
     * @ORM\ManyToOne(targetEntity=Categories::class, inversedBy="abouts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $idCategories;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCv(): ?string
    {
        return $this->cv;
    }

    public function setCv(string $cv): self
    {
        $this->cv = $cv;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContents2(): ?string
    {
        return $this->contents2;
    }

    public function setContents2(string $contents2): self
    {
        $this->contents2 = $contents2;

        return $this;
    }

    public function getIdCategories(): ?Categories
    {
        return $this->idCategories;
    }

    public function setIdCategories(?Categories $idCategories): self
    {
        $this->idCategories = $idCategories;

        return $this;
    }
}

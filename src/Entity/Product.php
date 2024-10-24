<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 70)]
    #[Groups([ 'api_posts_home'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups([ 'api_posts_home'])]
    private ?string $url = null;
    
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0, nullable: true)]
    #[Groups([ 'api_posts_home'])]
    private ?string $price = null;

    #[ORM\Column(length: 255)]
    #[Groups([ 'api_posts_home'])]
    private ?string $description = null;

    /**
     * @var Collection<int, ProductOption>
     */
    #[Groups([ 'api_posts_home'])]
    #[ORM\OneToMany(targetEntity: ProductOption::class, mappedBy: 'product', cascade: ['persist'])]
    private Collection $productOptions;

    public function __construct()
    {
        $this->productOptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Collection<int, ProductOption>
     */
    public function getProductOptions(): Collection
    {
        return $this->productOptions;
    }

    public function addProductOption(ProductOption $productOption): static
    {
        if (!$this->productOptions->contains($productOption)) {
            $this->productOptions->add($productOption);
            $productOption->setProduct($this);
        }

        return $this;
    }

    public function removeProductOption(ProductOption $productOption): static
    {
        if ($this->productOptions->removeElement($productOption)) {
            // set the owning side to null (unless already changed)
            if ($productOption->getProduct() === $this) {
                $productOption->setProduct(null);
            }
        }

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }
}

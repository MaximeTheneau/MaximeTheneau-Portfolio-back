<?php

namespace App\Entity;

use App\Repository\PostsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: PostsRepository::class)]
class Posts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['api_posts_browse', 'api_posts_read', 'api_posts_home', 'api_posts_desc', 'api_posts_sitemap','api_posts_related' ])]
    private ?int $id = null;

    #[ORM\Column(length: 70, unique: true, type: Types::STRING)]
    #[Groups(['api_posts_browse', 'api_posts_related', 'api_posts_read', 'api_posts_home', 'api_posts_desc', 'api_posts_category', 'api_posts_subcategory', 'api_posts_sitemap' ])]
    private ?string $title = null;

    #[ORM\Column(length: 65)]
    #[Groups(['api_posts_read', 'api_posts_home'])]
    private ?string $heading = null;

    #[ORM\Column(length: 160)]
    #[Groups(['api_posts_read', 'api_posts_home', 'api_posts_sitemap'])]
    private ?string $metaDescription = null;

    #[ORM\Column(length: 70, unique: true, type: Types::STRING)]
    #[Groups(['api_posts_browse', 'api_posts_related', 'api_posts_read', 'api_posts_home', 'api_posts_desc', 'api_posts_category', 'api_posts_subcategory' ])]
    private ?string $slug = null;
    
    #[ORM\Column(length: 5000, nullable: true, type: Types::STRING)]
    #[Groups(['api_posts_read', 'api_posts_browse', 'api_posts_home'])]
    private ?string $contents = null;

    #[ORM\Column]
    #[Groups(['api_posts_read', 'api_posts_sitemap', 'api_posts_home'])]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['api_posts_read', 'api_posts_sitemap', 'api_posts_home'])]
    private ?\DateTime $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'posts', targetEntity: ListPosts::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['api_posts_read', 'api_posts_home'])]
    private Collection $listPosts;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $links = null;

    #[ORM\OneToMany(mappedBy: 'posts', targetEntity: ParagraphPosts::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['api_posts_read', 'api_posts_home'])]
    private Collection $paragraphPosts;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $textLinks = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[Groups(['api_posts_read', 'api_posts_category', 'api_posts_home'])]
    private ?Category $category = null;

    #[ORM\ManyToMany(targetEntity: Subtopic::class, inversedBy: 'posts')]
    #[Groups(['api_posts_read', 'api_posts_home'])]
    private Collection $subtopic;

    #[ORM\Column(length: 125, nullable: true)]
    #[Groups(['api_posts_read', 'api_posts_home', 'api_posts_related',])]
    private ?string $altImg = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['api_posts_read', 'api_posts_sitemap', 'api_posts_home', 'api_posts_related', 'api_posts_subcategory', 'api_posts_category'])]
    private ?string $imgPost = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[Groups(['api_posts_browse', 'api_posts_category', 'api_posts_desc', 'api_posts_subcategory', 'api_posts_read', 'api_posts_home'])]
    private ?Subcategory $subcategory = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $video = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['api_posts_read', 'api_posts_home'])]
    private ?string $github = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['api_posts_read', 'api_posts_home'])]
    private ?string $website = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['api_posts_read', 'api_posts_related', 'api_posts_sitemap', 'api_posts_category', 'api_posts_home', 'api_posts_subcategory'])]
    private ?string $url = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isHomeImage = null;

    #[ORM\Column(length: 255)]
    #[Groups(['api_posts_read'])]
    private ?string $formattedDate = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'posts', cascade: ['persist'],)]
    #[ORM\JoinTable(name: 'posts_relations')]
    #[Groups(['api_posts_related'])]
    private Collection $relatedPosts;
    
    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'relatedPosts')]
    private Collection $posts;

    /**
     * @var Collection<int, Comments>
     */
    #[ORM\OneToMany(mappedBy: 'posts', targetEntity: Comments::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['api_posts_read'])]
    private Collection $comments;

    #[ORM\Column(nullable: true)]
    #[Groups(['api_posts_read', 'api_posts_home'])]
    private ?int $imgWidth = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['api_posts_read', 'api_posts_home'])]
    private ?int $imgHeight = null;

    #[ORM\Column(length: 1000, nullable: true)]
    #[Groups(['api_posts_read', 'api_posts_home'])]
    private ?string $srcset = null;

    /**
     * @var Collection<int, Skill>
     */
    #[ORM\ManyToMany(targetEntity: Skill::class, mappedBy: 'posts')]
    private Collection $skills;

    public function __construct()
    {
        $this->listPosts = new ArrayCollection();
        $this->paragraphPosts = new ArrayCollection();
        $this->subtopic = new ArrayCollection();
        $this->relatedPosts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->skills = new ArrayCollection();
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

    public function getContents(): ?string
    {
        return $this->contents;
    }

    public function setContents(string $contents): self
    {
        $this->contents = $contents;

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


    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, ListPosts>
     */
    public function getListPosts(): Collection
    {
        return $this->listPosts;
    }

    public function addListPost(ListPosts $listPost): self
    {
        if (!$this->listPosts->contains($listPost)) {
            $this->listPosts->add($listPost);
            $listPost->setPosts($this);
        }

        return $this;
    }

    public function removeListPost(ListPosts $listPost): self
    {
        if ($this->listPosts->removeElement($listPost)) {
            // set the owning side to null (unless already changed)
            if ($listPost->getPosts() === $this) {
                $listPost->setPosts(null);
            }
        }

        return $this;
    }

    public function getLinks(): ?string
    {
        return $this->links;
    }

    public function setLinks(?string $links): self
    {
        $this->links = $links;

        return $this;
    }

    /**
     * @return Collection<int, ParagraphPosts>
     */
    public function getParagraphPosts(): Collection
    {
        return $this->paragraphPosts;
    }

    public function addParagraphPost(ParagraphPosts $paragraphPost): self
    {
        if (!$this->paragraphPosts->contains($paragraphPost)) {
            $this->paragraphPosts->add($paragraphPost);
            $paragraphPost->setPosts($this);
        }

        return $this;
    }

    public function removeParagraphPost(ParagraphPosts $paragraphPost): self
    {
        if ($this->paragraphPosts->removeElement($paragraphPost)) {
            // set the owning side to null (unless already changed)
            if ($paragraphPost->getPosts() === $this) {
                $paragraphPost->setPosts(null);
            }
        }

        return $this;
    }

    public function getTextLinks(): ?string
    {
        return $this->textLinks;
    }

    public function setTextLinks(?string $textLinks): self
    {
        $this->textLinks = $textLinks;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Subtopic>
     */
    public function getSubtopic(): Collection
    {
        return $this->subtopic;
    }

    public function addSubtopic(Subtopic $subtopic): self
    {
        if (!$this->subtopic->contains($subtopic)) {
            $this->subtopic->add($subtopic);
        }

        return $this;
    }

    public function removeSubtopic(Subtopic $subtopic): self
    {
        $this->subtopic->removeElement($subtopic);

        return $this;
    }

    public function getAltImg(): ?string
    {
        return $this->altImg;
    }

    public function setAltImg(?string $altImg): self
    {
        $this->altImg = $altImg;

        return $this;
    }

    public function getImgPost(): ?string
    {
        return $this->imgPost;
    }

    public function setImgPost(?string $imgPost): self
    {
        $this->imgPost = $imgPost;

        return $this;
    }

    public function getSubcategory(): ?Subcategory
    {
        return $this->subcategory;
    }

    public function setSubcategory(?Subcategory $subcategory): self
    {
        $this->subcategory = $subcategory;

        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(?string $video): self
    {
        $this->video = $video;

        return $this;
    }

    public function getGithub(): ?string
    {
        return $this->github;
    }

    public function setGithub(?string $github): self
    {
        $this->github = $github;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(string $metaDescription): static
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getHeading(): ?string
    {
        return $this->heading;
    }

    public function setHeading(string $heading): static
    {
        $this->heading = $heading;

        return $this;
    }

    public function isHomeImage(): ?bool
    {
        return $this->isHomeImage;
    }

    public function setIsHomeImage(?bool $isHomeImage): static
    {
        $this->isHomeImage = $isHomeImage;

        return $this;
    }

    public function getFormattedDate(): ?string
    {
        return $this->formattedDate;
    }

    public function setFormattedDate(string $formattedDate): static
    {
        $this->formattedDate = $formattedDate;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getRelatedPosts(): Collection
    {
        return $this->relatedPosts;
    }

    public function addRelatedPost(self $relatedPost): static
    {
        if (!$this->relatedPosts->contains($relatedPost)) {
            $this->relatedPosts->add($relatedPost);
        }

        return $this;
    }

    public function removeRelatedPost(self $relatedPost): static
    {
        $this->relatedPosts->removeElement($relatedPost);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(self $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->addRelatedPost($this);
        }

        return $this;
    }

    public function removePost(self $post): static
    {
        if ($this->posts->removeElement($post)) {
            $post->removeRelatedPost($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Comments>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comments $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setCommentsPosts($this);
        }

        return $this;
    }

    public function removeComment(Comments $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getCommentsPosts() === $this) {
                $comment->setCommentsPosts(null);
            }
        }

        return $this;
    }

    public function getImgWidth(): ?int
    {
        return $this->imgWidth;
    }

    public function setImgWidth(?int $imgWidth): static
    {
        $this->imgWidth = $imgWidth;

        return $this;
    }

    public function getImgHeight(): ?int
    {
        return $this->imgHeight;
    }

    public function setImgHeight(?int $imgHeight): static
    {
        $this->imgHeight = $imgHeight;

        return $this;
    }

    public function getSrcset(): ?string
    {
        return $this->srcset;
    }

    public function setSrcset(?string $srcset): static
    {
        $this->srcset = $srcset;

        return $this;
    }

    /**
     * @return Collection<int, Skill>
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(Skill $skill): static
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
            $skill->addPost($this);
        }

        return $this;
    }

    public function removeSkill(Skill $skill): static
    {
        if ($this->skills->removeElement($skill)) {
            $skill->removePost($this);
        }

        return $this;
    }
}
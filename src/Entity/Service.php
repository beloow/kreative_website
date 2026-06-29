<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $title = null;

    #[ORM\Column(length: 50)]
    private ?string $icon = null; // nom d'icône (ex: "growth", "ads", "content", "seo")

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    private ?string $priceFrom = null; // ex: "à partir de 450€/mois"

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ctaUrl = null; // lien vers lequel le bouton "tarif" redirige (devis, contact, page externe...)

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column]
    private bool $isActive = true;

    /**
     * Date de mise à la corbeille. Null = service actif/visible, sinon en corbeille.
     */
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'services')]
    #[ORM\JoinTable(name: 'service_category')]
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): static
    {
        $this->icon = $icon;

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

    public function getPriceFrom(): ?string
    {
        return $this->priceFrom;
    }

    public function setPriceFrom(string $priceFrom): static
    {
        $this->priceFrom = $priceFrom;

        return $this;
    }

    public function getCtaUrl(): ?string
    {
        return $this->ctaUrl;
    }

    public function setCtaUrl(?string $ctaUrl): static
    {
        $this->ctaUrl = $ctaUrl;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function isTrashed(): bool
    {
        return null !== $this->deletedAt;
    }

    public function trash(): static
    {
        $this->deletedAt = new \DateTimeImmutable();

        return $this;
    }

    public function restore(): static
    {
        $this->deletedAt = null;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }
}

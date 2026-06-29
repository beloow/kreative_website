<?php

namespace App\Entity;

use App\Repository\PageViewRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageViewRepository::class)]
#[ORM\Index(columns: ['created_at'], name: 'idx_page_view_created_at')]
#[ORM\Index(columns: ['visitor_hash'], name: 'idx_page_view_visitor_hash')]
class PageView
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    /**
     * Identifiant anonyme du visiteur (hash IP + navigateur + jour), jamais l'IP en clair.
     * Permet d'estimer un nombre de "visiteurs uniques" sans stocker de donnée personnelle.
     */
    #[ORM\Column(length: 64)]
    private ?string $visitorHash = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getVisitorHash(): ?string
    {
        return $this->visitorHash;
    }

    public function setVisitorHash(string $visitorHash): static
    {
        $this->visitorHash = $visitorHash;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}

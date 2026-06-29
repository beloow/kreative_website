<?php

namespace App\Entity;

use App\Repository\SiteSettingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SiteSettingRepository::class)]
class SiteSetting
{
    public const SCHEME_LIGHT = 'light';
    public const SCHEME_DARK = 'dark';
    public const SCHEME_AUTO = 'auto';

    public const SCHEME_CHOICES = [
        'Sombre' => self::SCHEME_DARK,
        'Clair' => self::SCHEME_LIGHT,
        'Automatique (selon l\'appareil)' => self::SCHEME_AUTO,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $logoText = 'kreative·studio';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $faviconFilename = null;

    // ---- Thème du site public ----
    #[ORM\Column(length: 20)]
    private string $siteColorAccent = '#d2b48c';

    #[ORM\Column(length: 20)]
    private string $siteColorBackground = '#0A1726';

    #[ORM\Column(length: 60)]
    private string $siteFontHeading = 'Fraunces';

    #[ORM\Column(length: 60)]
    private string $siteFontBody = 'Inter';

    // ---- Thème de l'espace admin ----
    #[ORM\Column(length: 20)]
    private string $adminColorAccent = '#d2b48c';

    #[ORM\Column(length: 20)]
    private string $adminColorBackground = '#0A1726';

    #[ORM\Column(length: 10)]
    private string $adminColorScheme = self::SCHEME_DARK;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogoText(): string
    {
        return $this->logoText;
    }

    public function setLogoText(string $logoText): static
    {
        $this->logoText = $logoText;

        return $this;
    }

    public function getFaviconFilename(): ?string
    {
        return $this->faviconFilename;
    }

    public function setFaviconFilename(?string $faviconFilename): static
    {
        $this->faviconFilename = $faviconFilename;

        return $this;
    }

    public function getSiteColorAccent(): string
    {
        return $this->siteColorAccent;
    }

    public function setSiteColorAccent(string $value): static
    {
        $this->siteColorAccent = $value;

        return $this;
    }

    public function getSiteColorBackground(): string
    {
        return $this->siteColorBackground;
    }

    public function setSiteColorBackground(string $value): static
    {
        $this->siteColorBackground = $value;

        return $this;
    }

    public function getSiteFontHeading(): string
    {
        return $this->siteFontHeading;
    }

    public function setSiteFontHeading(string $value): static
    {
        $this->siteFontHeading = $value;

        return $this;
    }

    public function getSiteFontBody(): string
    {
        return $this->siteFontBody;
    }

    public function setSiteFontBody(string $value): static
    {
        $this->siteFontBody = $value;

        return $this;
    }

    public function getAdminColorAccent(): string
    {
        return $this->adminColorAccent;
    }

    public function setAdminColorAccent(string $value): static
    {
        $this->adminColorAccent = $value;

        return $this;
    }

    public function getAdminColorBackground(): string
    {
        return $this->adminColorBackground;
    }

    public function setAdminColorBackground(string $value): static
    {
        $this->adminColorBackground = $value;

        return $this;
    }

    public function getAdminColorScheme(): string
    {
        return $this->adminColorScheme;
    }

    public function setAdminColorScheme(string $value): static
    {
        $this->adminColorScheme = $value;

        return $this;
    }
}

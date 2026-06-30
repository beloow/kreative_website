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

    // ---- SEO ----
    #[ORM\Column(length: 70)]
    private string $seoHomeTitle = 'Kreative Studio — Marketing digital pour PME';

    #[ORM\Column(length: 160)]
    private string $seoHomeDescription = 'Kreative Studio délègue votre marketing digital : SEO, publicité, contenu et réseaux sociaux, pensés pour les PME qui veulent grandir sans recruter une équipe complète.';

    #[ORM\Column(length: 70)]
    private string $seoAboutTitle = 'À propos — Kreative Studio';

    #[ORM\Column(length: 160)]
    private string $seoAboutDescription = 'Kreative Studio accompagne les PME dans leur croissance digitale, avec la rigueur d\'une agence pour grands comptes et la souplesse d\'un studio à taille humaine.';

    #[ORM\Column(length: 70)]
    private string $seoServicesTitle = 'Services — Kreative Studio';

    #[ORM\Column(length: 160)]
    private string $seoServicesDescription = 'SEO, publicité en ligne, contenu, réseaux sociaux et reporting : découvrez nos services de marketing digital externalisé pour PME.';

    #[ORM\Column(length: 70)]
    private string $seoContactTitle = 'Contact — Kreative Studio';

    #[ORM\Column(length: 160)]
    private string $seoContactDescription = 'Parlons de votre croissance. Remplissez le formulaire, nous revenons vers vous en moins de 48h ouvrées.';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ogImageFilename = null;

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

    public function getSeoHomeTitle(): string
    {
        return $this->seoHomeTitle;
    }

    public function setSeoHomeTitle(string $value): static
    {
        $this->seoHomeTitle = $value;

        return $this;
    }

    public function getSeoHomeDescription(): string
    {
        return $this->seoHomeDescription;
    }

    public function setSeoHomeDescription(string $value): static
    {
        $this->seoHomeDescription = $value;

        return $this;
    }

    public function getSeoAboutTitle(): string
    {
        return $this->seoAboutTitle;
    }

    public function setSeoAboutTitle(string $value): static
    {
        $this->seoAboutTitle = $value;

        return $this;
    }

    public function getSeoAboutDescription(): string
    {
        return $this->seoAboutDescription;
    }

    public function setSeoAboutDescription(string $value): static
    {
        $this->seoAboutDescription = $value;

        return $this;
    }

    public function getSeoServicesTitle(): string
    {
        return $this->seoServicesTitle;
    }

    public function setSeoServicesTitle(string $value): static
    {
        $this->seoServicesTitle = $value;

        return $this;
    }

    public function getSeoServicesDescription(): string
    {
        return $this->seoServicesDescription;
    }

    public function setSeoServicesDescription(string $value): static
    {
        $this->seoServicesDescription = $value;

        return $this;
    }

    public function getSeoContactTitle(): string
    {
        return $this->seoContactTitle;
    }

    public function setSeoContactTitle(string $value): static
    {
        $this->seoContactTitle = $value;

        return $this;
    }

    public function getSeoContactDescription(): string
    {
        return $this->seoContactDescription;
    }

    public function setSeoContactDescription(string $value): static
    {
        $this->seoContactDescription = $value;

        return $this;
    }

    public function getOgImageFilename(): ?string
    {
        return $this->ogImageFilename;
    }

    public function setOgImageFilename(?string $value): static
    {
        $this->ogImageFilename = $value;

        return $this;
    }
}

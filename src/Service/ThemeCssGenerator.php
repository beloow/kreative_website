<?php

namespace App\Service;

use App\Entity\SiteSetting;

/**
 * Le style du site est piloté par des variables CSS statiques (public/css/style.css
 * et public/css/admin-theme.css). Plutôt que de réécrire ces fichiers entiers (risqué)
 * ou de dépendre d'un système de templating CSS dynamique, on génère simplement deux
 * petits fichiers de surcharge, chargés APRÈS les fichiers statiques, qui redéfinissent
 * juste les valeurs des variables couleurs/polices à partir des réglages en base.
 *
 * Le thème du site public et celui de l'admin sont entièrement indépendants.
 */
class ThemeCssGenerator
{
    public function __construct(private readonly string $publicDir)
    {
    }

    public function generate(SiteSetting $settings): void
    {
        $this->writePublicThemeCss($settings);
        $this->writeAdminThemeCss($settings);
    }

    private function writePublicThemeCss(SiteSetting $settings): void
    {
        $css = <<<CSS
/* Fichier généré automatiquement depuis /admin → Réglages → Thème du site. Ne pas modifier à la main. */
:root {
  --color-accent: {$settings->getSiteColorAccent()};
  --color-accent-soft: {$this->lighten($settings->getSiteColorAccent())};
  --color-bg: {$settings->getSiteColorBackground()};
  --font-display: '{$settings->getSiteFontHeading()}', 'Georgia', serif;
  --font-body: '{$settings->getSiteFontBody()}', -apple-system, sans-serif;
}
CSS;

        $this->writeFile($this->publicDir.'/css/theme-vars.css', $css);
    }

    private function writeAdminThemeCss(SiteSetting $settings): void
    {
        // Le mode "auto" laisse EasyAdmin gérer lui-même clair/sombre selon l'appareil ;
        // dans ce cas, on surcharge les variables des DEUX schémas pour rester cohérent.
        $applyToLight = \in_array($settings->getAdminColorScheme(), [SiteSetting::SCHEME_LIGHT, SiteSetting::SCHEME_AUTO], true);
        $applyToDark = \in_array($settings->getAdminColorScheme(), [SiteSetting::SCHEME_DARK, SiteSetting::SCHEME_AUTO], true);

        $css = "/* Fichier généré automatiquement depuis /admin → Réglages → Thème de l'admin. Ne pas modifier à la main. */\n";

        if ($applyToLight) {
            $css .= <<<CSS
:root {
  --color-primary: {$settings->getAdminColorAccent()};
  --link-color: {$settings->getAdminColorAccent()};
  --button-primary-bg: {$settings->getAdminColorAccent()};
}

CSS;
        }

        if ($applyToDark) {
            $css .= <<<CSS
.ea-dark-scheme {
  --true-gray-950: {$settings->getAdminColorBackground()};
  --color-primary: {$settings->getAdminColorAccent()};
  --button-primary-bg: {$settings->getAdminColorAccent()};
  --sidebar-menu-active-item-bg: {$settings->getAdminColorAccent()};
  --form-switch-checked-bg: {$settings->getAdminColorAccent()};
  --form-type-check-input-checked-bg: {$settings->getAdminColorAccent()};
  --pagination-active-bg: {$settings->getAdminColorAccent()};
  --badge-boolean-true-color: {$settings->getAdminColorAccent()};
}

CSS;
        }

        $css .= <<<CSS
.kd { --kd-tan: {$settings->getAdminColorAccent()}; --kd-navy: {$settings->getAdminColorBackground()}; }
.kc-avatar, .kd-period-btn.is-active, .kc-tag--pending { background: {$settings->getAdminColorAccent()}; }
CSS;

        $this->writeFile($this->publicDir.'/css/admin-theme-vars.css', $css);
    }

    private function writeFile(string $path, string $content): void
    {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents($path, $content);
    }

    /**
     * Calcule une variante plus claire de la couleur d'accent (pour les survols),
     * sans dépendance externe : on éclaircit chaque composante RGB de 40%.
     */
    private function lighten(string $hexColor): string
    {
        $hex = ltrim($hexColor, '#');
        if (6 !== \strlen($hex)) {
            return $hexColor;
        }

        $r = (int) hexdec(substr($hex, 0, 2));
        $g = (int) hexdec(substr($hex, 2, 2));
        $b = (int) hexdec(substr($hex, 4, 2));

        $r = min(255, (int) ($r + (255 - $r) * 0.4));
        $g = min(255, (int) ($g + (255 - $g) * 0.4));
        $b = min(255, (int) ($b + (255 - $b) * 0.4));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}

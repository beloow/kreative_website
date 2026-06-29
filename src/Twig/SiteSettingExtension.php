<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SiteSettingExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('site_settings', [SiteSettingRuntime::class, 'getSettings']),
        ];
    }
}

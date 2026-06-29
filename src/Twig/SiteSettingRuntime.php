<?php

namespace App\Twig;

use App\Entity\SiteSetting;
use App\Repository\SiteSettingRepository;
use Twig\Extension\RuntimeExtensionInterface;

class SiteSettingRuntime implements RuntimeExtensionInterface
{
    private ?SiteSetting $cached = null;

    public function __construct(private readonly SiteSettingRepository $siteSettingRepository)
    {
    }

    public function getSettings(): SiteSetting
    {
        return $this->cached ??= $this->siteSettingRepository->getSettings();
    }
}

<?php

namespace App\Command;

use App\Repository\SiteSettingRepository;
use App\Service\ThemeCssGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:generate-theme-css', description: 'Génère les fichiers CSS de thème à partir des réglages enregistrés')]
class GenerateThemeCssCommand extends Command
{
    public function __construct(
        private readonly SiteSettingRepository $siteSettingRepository,
        private readonly ThemeCssGenerator $themeCssGenerator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $settings = $this->siteSettingRepository->getSettings();
        $this->themeCssGenerator->generate($settings);

        $io->success('Fichiers CSS de thème générés (public/css/theme-vars.css et admin-theme-vars.css).');

        return Command::SUCCESS;
    }
}

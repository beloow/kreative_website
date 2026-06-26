<?php

namespace App\Command;

use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:seed-services', description: 'Insère les services de démonstration Kreative Studio')]
class SeedServicesCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $repository = $this->entityManager->getRepository(Service::class);

        if ($repository->count([]) > 0) {
            $io->warning('Des services existent déjà en base — aucune insertion effectuée.');

            return Command::SUCCESS;
        }

        $services = [
            ['growth', 'Stratégie de croissance', 'Un diagnostic complet de votre présence digitale et un plan d\'action priorisé selon votre budget et vos objectifs business.', 'à partir de 350€', 0],
            ['seo', 'Référencement naturel (SEO)', 'Optimisation technique, contenu et popularité pour faire remonter votre site sur les recherches qui comptent vraiment pour votre activité.', 'à partir de 490€/mois', 1],
            ['ads', 'Publicité en ligne (SEA & Social Ads)', 'Campagnes Google Ads et réseaux sociaux pilotées et optimisées chaque semaine pour maximiser votre retour sur investissement publicitaire.', 'à partir de 450€/mois + budget pub', 2],
            ['content', 'Création de contenu', 'Articles de blog, fiches produits et supports qui informent vos clients et nourrissent votre référencement naturel.', 'à partir de 390€/mois', 3],
            ['social', 'Réseaux sociaux', 'Gestion complète de vos comptes : ligne éditoriale, visuels, publication et animation de votre communauté.', 'à partir de 420€/mois', 4],
            ['analytics', 'Suivi & reporting', 'Tableaux de bord clairs pour suivre trafic, leads et conversions, avec un point mensuel pour ajuster la stratégie.', 'inclus dès 2 services combinés', 5],
        ];

        foreach ($services as [$icon, $title, $description, $price, $position]) {
            $service = new Service();
            $service->setIcon($icon);
            $service->setTitle($title);
            $service->setDescription($description);
            $service->setPriceFrom($price);
            $service->setPosition($position);
            $service->setIsActive(true);
            $this->entityManager->persist($service);
        }

        $this->entityManager->flush();
        $io->success('6 services de démonstration ont été créés.');

        return Command::SUCCESS;
    }
}

<?php

namespace App\Repository;

use App\Entity\SiteSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class SiteSettingRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct($registry, SiteSetting::class);
    }

    /**
     * Il n'existe qu'une seule ligne de réglages. On la crée avec les valeurs
     * par défaut si elle n'existe pas encore (premier accès après migration).
     */
    public function getSettings(): SiteSetting
    {
        $settings = $this->findOneBy([]);

        if (null === $settings) {
            $settings = new SiteSetting();
            $this->entityManager->persist($settings);
            $this->entityManager->flush();
        }

        return $settings;
    }
}

<?php

namespace App\Repository;

use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    /**
     * @return Service[]
     */
    public function findActiveOrdered(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.isActive = true')
            ->andWhere('s.deletedAt IS NULL')
            ->orderBy('s.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Nombre total de services hors corbeille.
     */
    public function countNotTrashed(): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Nombre de services actifs (visibles sur le site) hors corbeille.
     */
    public function countActiveNotTrashed(): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.isActive = true')
            ->andWhere('s.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }
}

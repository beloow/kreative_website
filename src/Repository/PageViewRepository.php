<?php

namespace App\Repository;

use App\Entity\PageView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PageViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageView::class);
    }

    public function countSince(\DateTimeImmutable $since): int
    {
        return (int) $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->andWhere('v.createdAt >= :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countUniqueVisitorsSince(\DateTimeImmutable $since): int
    {
        return (int) $this->createQueryBuilder('v')
            ->select('COUNT(DISTINCT v.visitorHash)')
            ->andWhere('v.createdAt >= :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Nombre de vues par jour sur les N derniers jours (jours sans visite inclus, à 0).
     *
     * @return array<int, array{label: string, count: int}>
     */
    public function countByDay(int $days = 30): array
    {
        $start = (new \DateTimeImmutable('today'))->modify(sprintf('-%d days', $days - 1));

        $views = $this->createQueryBuilder('v')
            ->andWhere('v.createdAt >= :start')
            ->setParameter('start', $start)
            ->getQuery()
            ->getResult();

        $buckets = [];
        for ($i = 0; $i < $days; ++$i) {
            $day = $start->modify(sprintf('+%d days', $i));
            $buckets[$day->format('Y-m-d')] = [
                'label' => $day->format('d/m'),
                'count' => 0,
            ];
        }

        foreach ($views as $view) {
            $key = $view->getCreatedAt()->format('Y-m-d');
            if (isset($buckets[$key])) {
                ++$buckets[$key]['count'];
            }
        }

        return array_values($buckets);
    }

    /**
     * Les pages les plus consultées depuis une date donnée.
     *
     * @return array<int, array{path: string, count: int}>
     */
    public function topPagesSince(\DateTimeImmutable $since, int $limit = 8): array
    {
        return $this->createQueryBuilder('v')
            ->select('v.path AS path, COUNT(v.id) AS count')
            ->andWhere('v.createdAt >= :since')
            ->setParameter('since', $since)
            ->groupBy('v.path')
            ->orderBy('count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}

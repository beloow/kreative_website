<?php

namespace App\Repository;

use App\Entity\ContactMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ContactMessageRepository extends ServiceEntityRepository
{
    private const FRENCH_MONTHS = ['jan', 'fév', 'mar', 'avr', 'mai', 'jun', 'jul', 'aoû', 'sep', 'oct', 'nov', 'déc'];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactMessage::class);
    }

    /**
     * Retourne le nombre de demandes reçues, mois par mois, sur les N derniers mois
     * (y compris les mois sans aucune demande, à 0).
     *
     * @return array<int, array{label: string, count: int}>
     */
    public function countByMonth(int $months = 6): array
    {
        $start = (new \DateTimeImmutable('first day of this month'))->modify(sprintf('-%d months', $months - 1));

        $messages = $this->createQueryBuilder('m')
            ->andWhere('m.createdAt >= :start')
            ->setParameter('start', $start)
            ->getQuery()
            ->getResult();

        $buckets = [];
        for ($i = 0; $i < $months; ++$i) {
            $month = $start->modify(sprintf('+%d months', $i));
            $buckets[$month->format('Y-m')] = [
                'label' => self::FRENCH_MONTHS[(int) $month->format('n') - 1],
                'count' => 0,
            ];
        }

        foreach ($messages as $message) {
            $key = $message->getCreatedAt()->format('Y-m');
            if (isset($buckets[$key])) {
                ++$buckets[$key]['count'];
            }
        }

        return array_values($buckets);
    }

    public function countSince(\DateTimeImmutable $since): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.createdAt >= :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

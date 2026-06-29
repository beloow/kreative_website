<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Calcule des séries temporelles (nombre d'éléments par tranche de temps) pour n'importe
 * quelle entité disposant d'un champ `createdAt`, sur une période choisie parmi une liste fixe.
 * Évite de dupliquer cette logique dans chaque repository (PageView, ContactMessage...).
 */
class TimeSeriesStats
{
    private const FRENCH_MONTHS = ['jan', 'fév', 'mar', 'avr', 'mai', 'jun', 'jul', 'aoû', 'sep', 'oct', 'nov', 'déc'];

    public const PERIODS = [
        'today' => "Aujourd'hui",
        '7d' => '7 jours',
        '1m' => '1 mois',
        '3m' => '3 mois',
        '6m' => '6 mois',
        'year' => 'Année en cours',
    ];

    public const DEFAULT_PERIOD = '1m';

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public static function isValidPeriod(?string $period): bool
    {
        return null !== $period && \array_key_exists($period, self::PERIODS);
    }

    /**
     * @return array<int, array{label: string, count: int}>
     */
    public function countByPeriod(string $entityClass, string $period): array
    {
        if (!self::isValidPeriod($period)) {
            $period = self::DEFAULT_PERIOD;
        }

        [$start, $unit, $bucketCount, $keyFormat, $labelFormat] = $this->resolvePeriod($period);

        $items = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from($entityClass, 'e')
            ->andWhere('e.createdAt >= :start')
            ->setParameter('start', $start)
            ->getQuery()
            ->getResult();

        $buckets = [];
        for ($i = 0; $i < $bucketCount; ++$i) {
            $date = $this->advance($start, $unit, $i);
            $buckets[$date->format($keyFormat)] = [
                'label' => $this->formatLabel($date, $labelFormat, $i),
                'count' => 0,
            ];
        }

        foreach ($items as $item) {
            $key = $item->getCreatedAt()->format($keyFormat);
            if (isset($buckets[$key])) {
                ++$buckets[$key]['count'];
            }
        }

        return array_values($buckets);
    }

    /**
     * @return array{0: \DateTimeImmutable, 1: string, 2: int, 3: string, 4: string}
     */
    private function resolvePeriod(string $period): array
    {
        $now = new \DateTimeImmutable();

        return match ($period) {
            'today' => [new \DateTimeImmutable('today'), 'hour', 24, 'Y-m-d H', 'hour'],
            '7d' => [(new \DateTimeImmutable('today'))->modify('-6 days'), 'day', 7, 'Y-m-d', 'day'],
            '1m' => [(new \DateTimeImmutable('today'))->modify('-29 days'), 'day', 30, 'Y-m-d', 'index'],
            '3m' => [$this->startOfWeek($now)->modify('-12 weeks'), 'week', 13, 'o-W', 'week'],
            '6m' => [(new \DateTimeImmutable('first day of this month'))->modify('-5 months'), 'month', 6, 'Y-m', 'month'],
            'year' => [new \DateTimeImmutable('first day of January this year'), 'month', (int) $now->format('n'), 'Y-m', 'month'],
            default => [(new \DateTimeImmutable('today'))->modify('-29 days'), 'day', 30, 'Y-m-d', 'index'],
        };
    }

    private function advance(\DateTimeImmutable $date, string $unit, int $steps): \DateTimeImmutable
    {
        return match ($unit) {
            'hour' => $date->modify(sprintf('+%d hours', $steps)),
            'week' => $date->modify(sprintf('+%d weeks', $steps)),
            'month' => $date->modify(sprintf('+%d months', $steps)),
            default => $date->modify(sprintf('+%d days', $steps)),
        };
    }

    private function startOfWeek(\DateTimeImmutable $date): \DateTimeImmutable
    {
        return $date->modify('monday this week')->setTime(0, 0);
    }

    private function formatLabel(\DateTimeImmutable $date, string $labelFormat, int $index): string
    {
        return match ($labelFormat) {
            'hour' => $date->format('H\h'),
            'week' => 'S' . $date->format('W'),
            'month' => self::FRENCH_MONTHS[(int) $date->format('n') - 1],
            'index' => (string) ($index + 1),
            default => $date->format('d/m'),
        };
    }
}

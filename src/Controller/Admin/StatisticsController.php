<?php

namespace App\Controller\Admin;

use App\Repository\ContactMessageRepository;
use App\Repository\PageViewRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class StatisticsController extends AbstractController
{
    private const TRAFFIC_CHART_WIDTH = 900;
    private const TRAFFIC_CHART_HEIGHT = 220;
    private const TRAFFIC_CHART_PAD_X = 24;
    private const TRAFFIC_CHART_PAD_Y = 20;

    public function __construct(
        private readonly PageViewRepository $pageViewRepository,
        private readonly ServiceRepository $serviceRepository,
        private readonly ContactMessageRepository $contactMessageRepository,
    ) {
    }

    #[Route('/admin/statistiques', name: 'admin_statistics')]
    public function index(): Response
    {
        $todayStart = new \DateTimeImmutable('today');
        $monthStart = new \DateTimeImmutable('first day of this month');
        $epoch = new \DateTimeImmutable('1970-01-01');
        $last30DaysStart = $todayStart->modify('-29 days');

        $totalViews = $this->pageViewRepository->countSince($epoch);
        $viewsToday = $this->pageViewRepository->countSince($todayStart);
        $viewsThisMonth = $this->pageViewRepository->countSince($monthStart);
        $uniqueVisitorsThisMonth = $this->pageViewRepository->countUniqueVisitorsSince($monthStart);
        $uniqueVisitorsTotal = $this->pageViewRepository->countUniqueVisitorsSince($epoch);

        $dailyCounts = $this->pageViewRepository->countByDay(30);
        $topPages = $this->pageViewRepository->topPagesSince($last30DaysStart, 8);

        $totalServices = $this->serviceRepository->count([]);
        $activeServices = $this->serviceRepository->count(['isActive' => true]);

        $totalMessages = $this->contactMessageRepository->count([]);
        $handledMessages = $this->contactMessageRepository->count(['isHandled' => true]);
        $pendingMessages = $totalMessages - $handledMessages;
        $messagesThisMonth = $this->contactMessageRepository->countSince($monthStart);
        $monthlyContactCounts = $this->contactMessageRepository->countByMonth(6);

        return $this->render('admin/statistics.html.twig', [
            'totalViews' => $totalViews,
            'viewsToday' => $viewsToday,
            'viewsThisMonth' => $viewsThisMonth,
            'uniqueVisitorsThisMonth' => $uniqueVisitorsThisMonth,
            'uniqueVisitorsTotal' => $uniqueVisitorsTotal,
            'topPages' => $topPages,
            'totalServices' => $totalServices,
            'activeServices' => $activeServices,
            'totalMessages' => $totalMessages,
            'handledMessages' => $handledMessages,
            'pendingMessages' => $pendingMessages,
            'messagesThisMonth' => $messagesThisMonth,
            'handledPercent' => $totalMessages > 0 ? round($handledMessages / $totalMessages * 100) : 0,
            'pendingPercent' => $totalMessages > 0 ? round($pendingMessages / $totalMessages * 100) : 0,
            'chartWidth' => self::TRAFFIC_CHART_WIDTH,
            'chartHeight' => self::TRAFFIC_CHART_HEIGHT,
            'chartPoints' => $this->buildChartPoints($dailyCounts, self::TRAFFIC_CHART_WIDTH, self::TRAFFIC_CHART_HEIGHT, self::TRAFFIC_CHART_PAD_X, self::TRAFFIC_CHART_PAD_Y),
            'contactChartPoints' => $this->buildChartPoints($monthlyContactCounts, self::TRAFFIC_CHART_WIDTH, self::TRAFFIC_CHART_HEIGHT, self::TRAFFIC_CHART_PAD_X, self::TRAFFIC_CHART_PAD_Y),
        ]);
    }

    /**
     * @param array<int, array{label: string, count: int}> $counts
     *
     * @return array<int, array{x: float, y: float, label: string, count: int}>
     */
    private function buildChartPoints(array $counts, int $width, int $height, int $padX, int $padY): array
    {
        $innerWidth = $width - 2 * $padX;
        $innerHeight = $height - 2 * $padY;
        $maxValue = max(1, max(array_column($counts, 'count')));
        $total = count($counts);

        $points = [];
        foreach ($counts as $i => $bucket) {
            $x = $total > 1
                ? $padX + ($innerWidth * $i / ($total - 1))
                : $padX + $innerWidth / 2;
            $y = $padY + $innerHeight - ($innerHeight * $bucket['count'] / $maxValue);

            $points[] = [
                'x' => round($x, 1),
                'y' => round($y, 1),
                'label' => $bucket['label'],
                'count' => $bucket['count'],
            ];
        }

        return $points;
    }
}

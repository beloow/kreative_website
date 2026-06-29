<?php

namespace App\Controller\Admin;

use App\Entity\ContactMessage;
use App\Entity\PageView;
use App\Entity\Service;
use App\Entity\User;
use App\Repository\ContactMessageRepository;
use App\Repository\PageViewRepository;
use App\Repository\ServiceRepository;
use App\Service\TimeSeriesStats;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    private const CHART_WIDTH = 600;
    private const CHART_HEIGHT = 220;
    private const CHART_PAD_X = 24;
    private const CHART_PAD_Y = 20;

    private const TRAFFIC_CHART_WIDTH = 900;
    private const TRAFFIC_CHART_HEIGHT = 220;
    private const TRAFFIC_CHART_PAD_X = 24;
    private const TRAFFIC_CHART_PAD_Y = 20;

    public function __construct(
        private readonly ServiceRepository $serviceRepository,
        private readonly ContactMessageRepository $contactMessageRepository,
        private readonly PageViewRepository $pageViewRepository,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly TimeSeriesStats $timeSeriesStats,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * Route unique et reconnue par EasyAdmin (celle déclarée via #[AdminDashboard]).
     * On bascule entre "Tableau de bord" et "Statistiques" via ?view=statistics,
     * plutôt que via une route séparée — certaines versions d'EasyAdmin ne
     * construisent correctement leur contexte interne que pour cette route précise.
     */
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $request = $this->requestStack->getCurrentRequest();

        if ('statistics' === $request?->query->get('view')) {
            return $this->renderStatistics($request);
        }

        return $this->renderDashboard($request);
    }

    private function renderDashboard(?Request $request): Response
    {
        $totalServices = $this->serviceRepository->count([]);
        $activeServices = $this->serviceRepository->count(['isActive' => true]);

        $totalMessages = $this->contactMessageRepository->count([]);
        $handledMessages = $this->contactMessageRepository->count(['isHandled' => true]);
        $pendingMessages = $totalMessages - $handledMessages;

        $recentMessages = $this->contactMessageRepository->findBy([], ['createdAt' => 'DESC'], 5);

        $contactPeriod = $request?->query->get('contact_period');
        if (!TimeSeriesStats::isValidPeriod($contactPeriod)) {
            $contactPeriod = '6m';
        }
        $contactCounts = $this->timeSeriesStats->countByPeriod(ContactMessage::class, $contactPeriod);

        $messagesIndexUrl = $this->adminUrlGenerator
            ->setController(ContactMessageCrudController::class)
            ->setAction('index')
            ->generateUrl();

        return $this->render('admin/dashboard.html.twig', [
            'totalServices' => $totalServices,
            'activeServices' => $activeServices,
            'totalMessages' => $totalMessages,
            'handledMessages' => $handledMessages,
            'pendingMessages' => $pendingMessages,
            'handledPercent' => $totalMessages > 0 ? round($handledMessages / $totalMessages * 100) : 0,
            'pendingPercent' => $totalMessages > 0 ? round($pendingMessages / $totalMessages * 100) : 0,
            'recentMessages' => $recentMessages,
            'messagesIndexUrl' => $messagesIndexUrl,
            'chartWidth' => self::CHART_WIDTH,
            'chartHeight' => self::CHART_HEIGHT,
            'chartPoints' => $this->buildChartPoints($contactCounts, self::CHART_WIDTH, self::CHART_HEIGHT, self::CHART_PAD_X, self::CHART_PAD_Y),
            'contactPeriod' => $contactPeriod,
            'periods' => TimeSeriesStats::PERIODS,
        ]);
    }

    private function renderStatistics(Request $request): Response
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

        $topPages = $this->pageViewRepository->topPagesSince($last30DaysStart, 8);

        $totalServices = $this->serviceRepository->count([]);
        $activeServices = $this->serviceRepository->count(['isActive' => true]);

        $totalMessages = $this->contactMessageRepository->count([]);
        $handledMessages = $this->contactMessageRepository->count(['isHandled' => true]);
        $pendingMessages = $totalMessages - $handledMessages;
        $messagesThisMonth = $this->contactMessageRepository->countSince($monthStart);

        $trafficPeriod = $request->query->get('traffic_period');
        if (!TimeSeriesStats::isValidPeriod($trafficPeriod)) {
            $trafficPeriod = TimeSeriesStats::DEFAULT_PERIOD;
        }

        $contactPeriod = $request->query->get('contact_period');
        if (!TimeSeriesStats::isValidPeriod($contactPeriod)) {
            $contactPeriod = '6m';
        }

        $trafficCounts = $this->timeSeriesStats->countByPeriod(PageView::class, $trafficPeriod);
        $contactCounts = $this->timeSeriesStats->countByPeriod(ContactMessage::class, $contactPeriod);

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
            'chartPoints' => $this->buildChartPoints($trafficCounts, self::TRAFFIC_CHART_WIDTH, self::TRAFFIC_CHART_HEIGHT, self::TRAFFIC_CHART_PAD_X, self::TRAFFIC_CHART_PAD_Y),
            'contactChartPoints' => $this->buildChartPoints($contactCounts, self::TRAFFIC_CHART_WIDTH, self::TRAFFIC_CHART_HEIGHT, self::TRAFFIC_CHART_PAD_X, self::TRAFFIC_CHART_PAD_Y),
            'trafficPeriod' => $trafficPeriod,
            'contactPeriod' => $contactPeriod,
            'periods' => TimeSeriesStats::PERIODS,
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

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Kreative Studio — Administration')
            ->setFaviconPath('favicon.svg');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile('css/admin-theme.css')
            ->addCssFile('css/admin-dashboard.css');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::linkToRoute('Statistiques', 'fa fa-chart-line', 'admin', ['view' => 'statistics']);
        yield MenuItem::linkToCrud('Services', 'fa fa-bullhorn', Service::class);
        yield MenuItem::linkToCrud('Demandes de contact', 'fa fa-envelope', ContactMessage::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class)->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToUrl('Voir le site', 'fa fa-globe', '/');
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\ContactMessage;
use App\Entity\Service;
use App\Repository\ContactMessageRepository;
use App\Repository\ServiceRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    private const CHART_WIDTH = 600;
    private const CHART_HEIGHT = 220;
    private const CHART_PAD_X = 24;
    private const CHART_PAD_Y = 20;

    public function __construct(
        private readonly ServiceRepository $serviceRepository,
        private readonly ContactMessageRepository $contactMessageRepository,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $totalServices = $this->serviceRepository->count([]);
        $activeServices = $this->serviceRepository->count(['isActive' => true]);

        $totalMessages = $this->contactMessageRepository->count([]);
        $handledMessages = $this->contactMessageRepository->count(['isHandled' => true]);
        $pendingMessages = $totalMessages - $handledMessages;

        $recentMessages = $this->contactMessageRepository->findBy([], ['createdAt' => 'DESC'], 5);
        $monthlyCounts = $this->contactMessageRepository->countByMonth(6);

        $messagesIndexUrl = $this->adminUrlGenerator
            ->setController(\App\Controller\Admin\ContactMessageCrudController::class)
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
            'chartPoints' => $this->buildChartPoints($monthlyCounts),
        ]);
    }

    /**
     * Transforme les comptages mensuels en coordonnées SVG (x, y) prêtes à afficher,
     * dans le même esprit que la "courbe de croissance" du site public.
     *
     * @param array<int, array{label: string, count: int}> $monthlyCounts
     *
     * @return array<int, array{x: float, y: float, label: string, count: int}>
     */
    private function buildChartPoints(array $monthlyCounts): array
    {
        $innerWidth = self::CHART_WIDTH - 2 * self::CHART_PAD_X;
        $innerHeight = self::CHART_HEIGHT - 2 * self::CHART_PAD_Y;
        $maxValue = max(1, max(array_column($monthlyCounts, 'count')));
        $total = count($monthlyCounts);

        $points = [];
        foreach ($monthlyCounts as $i => $bucket) {
            $x = $total > 1
                ? self::CHART_PAD_X + ($innerWidth * $i / ($total - 1))
                : self::CHART_PAD_X + $innerWidth / 2;
            $y = self::CHART_PAD_Y + $innerHeight - ($innerHeight * $bucket['count'] / $maxValue);

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
        yield MenuItem::linkToRoute('Statistiques', 'fa fa-chart-line', 'admin_statistics');
        yield MenuItem::linkToCrud('Services', 'fa fa-bullhorn', Service::class);
        yield MenuItem::linkToCrud('Demandes de contact', 'fa fa-envelope', ContactMessage::class);
        yield MenuItem::linkToUrl('Voir le site', 'fa fa-globe', '/');
    }
}

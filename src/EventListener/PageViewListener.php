<?php

namespace App\EventListener;

use App\Entity\PageView;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Enregistre une visite anonyme pour chaque page publique du site, sans jamais
 * stocker l'adresse IP en clair : on ne conserve qu'un hash (IP + navigateur + jour),
 * suffisant pour estimer un nombre de "visiteurs uniques" sans donnée personnelle.
 */
class PageViewListener implements EventSubscriberInterface
{
    private const EXCLUDED_PREFIXES = ['/admin', '/css', '/images', '/_wdt', '/_profiler', '/favicon'];

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'onKernelResponse'];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ('GET' !== $request->getMethod()) {
            return;
        }

        if (200 !== $event->getResponse()->getStatusCode()) {
            return;
        }

        if ('XMLHttpRequest' === $request->headers->get('X-Requested-With')) {
            return;
        }

        $path = $request->getPathInfo();

        foreach (self::EXCLUDED_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return;
            }
        }

        $visitorHash = hash('sha256', sprintf(
            '%s|%s|%s',
            $request->getClientIp() ?? 'unknown',
            $request->headers->get('User-Agent', 'unknown'),
            (new \DateTimeImmutable())->format('Y-m-d')
        ));

        $pageView = new PageView();
        $pageView->setPath($path);
        $pageView->setVisitorHash($visitorHash);

        $this->entityManager->persist($pageView);
        $this->entityManager->flush();
    }
}

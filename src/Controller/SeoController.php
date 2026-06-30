<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SeoController extends AbstractController
{
    #[Route('/robots.txt', name: 'robots_txt')]
    public function robots(): Response
    {
        $content = <<<TXT
User-agent: *
Allow: /
Disallow: /admin

Sitemap: {$this->generateUrl('sitemap_xml', [], UrlGeneratorInterface::ABSOLUTE_URL)}
TXT;

        return new Response($content, 200, ['Content-Type' => 'text/plain']);
    }

    #[Route('/sitemap.xml', name: 'sitemap_xml')]
    public function sitemap(): Response
    {
        $routes = ['home', 'about', 'service_index', 'contact'];
        $today = (new \DateTimeImmutable())->format('Y-m-d');

        $urls = '';
        foreach ($routes as $route) {
            $loc = $this->generateUrl($route, [], UrlGeneratorInterface::ABSOLUTE_URL);
            $urls .= <<<XML
    <url>
        <loc>{$loc}</loc>
        <lastmod>{$today}</lastmod>
    </url>

XML;
        }

        $content = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
{$urls}</urlset>
XML;

        return new Response($content, 200, ['Content-Type' => 'application/xml']);
    }
}

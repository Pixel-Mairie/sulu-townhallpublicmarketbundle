<?php

declare(strict_types=1);

namespace Pixel\TownHallPublicMarketBundle\Controller\Website;

use Pixel\TownHallPublicMarketBundle\Entity\PublicMarket;
use Sulu\Bundle\PreviewBundle\Preview\Preview;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;
use Sulu\Bundle\WebsiteBundle\Resolver\TemplateAttributeResolverInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PublicMarketController extends AbstractController
{
    private TemplateAttributeResolverInterface $templateAttributeResolver;

    private RouteRepositoryInterface $routeRepository;

    private WebspaceManagerInterface $webspaceManager;

    public function __construct(TemplateAttributeResolverInterface $templateAttributeResolver, RouteRepositoryInterface $routeRepository, WebspaceManagerInterface $webspaceManager)
    {
        $this->templateAttributeResolver = $templateAttributeResolver;
        $this->routeRepository = $routeRepository;
        $this->webspaceManager = $webspaceManager;
    }

    /**
     * @param array<mixed> $attributes
     * @throws \Exception
     */
    public function indexAction(PublicMarket $publicMarket, array $attributes = [], bool $preview = false, bool $partial = false): Response
    {
        if (! $publicMarket->getSeo() || (isset($publicMarket->getSeo()['title']) && ! $publicMarket->getSeo()['title'])) {
            $seo = [
                'title' => $publicMarket->getTitle(),
            ];
            $publicMarket->setSeo($seo);
        }

        $parameters = $this->templateAttributeResolver->resolve([
            'publicMarket' => $publicMarket,
            'localizations' => $this->getLocalizationsArrayForEntity($publicMarket),
        ]);

        if ($partial) {
            return $this->renderBlock(
                "@TownHallPublicMarket/public_market.html.twig",
                "content",
                $parameters
            );
        } elseif ($preview) {
            $content = $this->renderPreview(
                "@TownHallPublicMarket/public_market.html.twig",
                $parameters
            );
        } else {
            if (! $publicMarket->isActive()) {
                throw $this->createNotFoundException();
            }
            $content = $this->renderView(
                "@TownHallPublicMarket/public_market.html.twig",
                $parameters
            );
        }

        return new Response($content);
    }

    /**
     * @return array<string, array<mixed>>
     */
    protected function getLocalizationsArrayForEntity(PublicMarket $entity): array
    {
        $routes = $this->routeRepository->findAllByEntity(PublicMarket::class, (string) $entity->getId());

        $localizations = [];
        foreach ($routes as $route) {
            $url = $this->webspaceManager->findUrlByResourceLocator(
                $route->getPath(),
                null,
                $route->getLocale()
            );

            $localizations[$route->getLocale()] = [
                'locale' => $route->getLocale(),
                'url' => $url,
            ];
        }

        return $localizations;
    }

    /**
     * @param array<string> $parameters
     */
    protected function renderPreview(string $view, array $parameters = []): string
    {
        $parameters['previewParentTemplate'] = $view;
        $parameters['previewContentReplacer'] = Preview::CONTENT_REPLACER;

        return $this->renderView('@SuluWebsite/Preview/preview.html.twig', $parameters);
    }
}

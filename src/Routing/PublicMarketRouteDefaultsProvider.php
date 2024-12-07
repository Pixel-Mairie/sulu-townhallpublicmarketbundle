<?php

namespace Pixel\TownHallPublicMarketBundle\Routing;

use Pixel\TownHallPublicMarketBundle\Controller\Website\PublicMarketController;
use Pixel\TownHallPublicMarketBundle\Entity\PublicMarket;
use Pixel\TownHallPublicMarketBundle\Repository\PublicMarketRepository;
use Sulu\Bundle\RouteBundle\Routing\Defaults\RouteDefaultsProviderInterface;

class PublicMarketRouteDefaultsProvider implements RouteDefaultsProviderInterface
{
    private PublicMarketRepository $publicMarketRepository;

    public function __construct(PublicMarketRepository $publicMarketRepository)
    {
        $this->publicMarketRepository = $publicMarketRepository;
    }

    /**
     * @return mixed[]
     */
    public function getByEntity($entityClass, $id, $locale, $object = null)
    {
        return [
            '_controller' => PublicMarketController::class . "::indexAction",
            'publicMarket' => $object ?: $this->publicMarketRepository->findById((int) $id, $locale),
        ];
    }

    public function isPublished($entityClass, $id, $locale)
    {
        return true;
    }

    public function supports($entityClass)
    {
        return PublicMarket::class === $entityClass;
    }
}

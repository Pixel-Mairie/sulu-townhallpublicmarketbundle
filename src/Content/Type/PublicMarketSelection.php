<?php

declare(strict_types=1);

namespace Pixel\TownHallPublicMarketBundle\Content\Type;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\TownHallPublicMarketBundle\Entity\PublicMarket;
use Sulu\Bundle\ReferenceBundle\Application\Collector\ReferenceCollectorInterface;
use Sulu\Bundle\ReferenceBundle\Infrastructure\Sulu\ContentType\ReferenceContentTypeInterface;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class PublicMarketSelection extends SimpleContentType implements ReferenceContentTypeInterface
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct('public_market_selection', []);
    }

    public function getContentData(PropertyInterface $property)
    {
        $ids = $property->getValue();

        if (empty($ids)) {
            return [];
        }

        $publicsMarkets = $this->entityManager->getRepository(PublicMarket::class)->findBy([
            'id' => $ids,
        ]);
        $idPositions = array_flip($ids);
        usort($publicsMarkets, function (PublicMarket $a, PublicMarket $b) use ($idPositions) {
            return $idPositions[$a->getId()] - $idPositions[$b->getId()];
        });
        return $publicsMarkets;
    }

    public function getViewData(PropertyInterface $property)
    {
        return [
            'ids' => $property->getValue(),
        ];
    }

    public function getReferences(PropertyInterface $property, ReferenceCollectorInterface $referenceCollector, string $propertyPrefix = ''): void
    {
        $data = $property->getValue();
        if (! isset($data) || ! is_array($data)) {
            return;
        }

        foreach ($data as $id) {
            $referenceCollector->addReference(
                PublicMarket::RESOURCE_KEY,
                (string) $id,
                $propertyPrefix . $property->getName()
            );
        }
    }
}

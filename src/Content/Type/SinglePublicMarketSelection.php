<?php

declare(strict_types=1);

namespace Pixel\TownHallPublicMarketBundle\Content\Type;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\TownHallPublicMarketBundle\Entity\PublicMarket;
use Sulu\Bundle\ReferenceBundle\Application\Collector\ReferenceCollectorInterface;
use Sulu\Bundle\ReferenceBundle\Infrastructure\Sulu\ContentType\ReferenceContentTypeInterface;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class SinglePublicMarketSelection extends SimpleContentType implements ReferenceContentTypeInterface
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct("single_public_market_selection", []);
    }

    public function getContentData(PropertyInterface $property)
    {
        $id = $property->getValue();

        if (empty($id)) {
            return [];
        }

        return $this->entityManager->getRepository(PublicMarket::class)->find($id);
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
        if (! isset($data) || ! is_int($data)) {
            return;
        }

        $referenceCollector->addReference(
            PublicMarket::RESOURCE_KEY,
            (string) $data,
            $propertyPrefix . $property->getName()
        );
    }
}

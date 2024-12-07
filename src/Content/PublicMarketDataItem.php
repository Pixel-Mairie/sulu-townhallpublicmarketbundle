<?php

declare(strict_types=1);

namespace Pixel\TownHallPublicMarketBundle\Content;

use JMS\Serializer\Annotation as Serializer;
use Pixel\TownHallPublicMarketBundle\Entity\PublicMarket;
use Sulu\Component\SmartContent\ItemInterface;

/**
 * @Serializer\ExclusionPolicy("all")
 */
class PublicMarketDataItem implements ItemInterface
{
    private PublicMarket $entity;

    public function __construct(PublicMarket $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getId(): string
    {
        return (string) $this->entity->getId();
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getTitle(): string
    {
        return (string) $this->entity->getTitle();
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getImage(): ?string
    {
        return null;
    }

    public function getResource(): PublicMarket
    {
        return $this->entity;
    }
}

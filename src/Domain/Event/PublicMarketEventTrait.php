<?php

declare(strict_types=1);

namespace Pixel\TownHallPublicMarketBundle\Domain\Event;

use Pixel\TownHallPublicMarketBundle\Entity\PublicMarket;

trait PublicMarketEventTrait
{
    public function getResourceKey(): string
    {
        return PublicMarket::RESOURCE_KEY;
    }

    public function getResourceId(): string
    {
        return (string) $this->publicMarket->getId();
    }

    public function getResourceTitle(): ?string
    {
        return $this->publicMarket->getTitle();
    }

    public function getResourceSecurityContext(): ?string
    {
        return PublicMarket::SECURITY_CONTEXT;
    }
}
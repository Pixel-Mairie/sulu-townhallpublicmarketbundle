<?php

declare(strict_types=1);

namespace Pixel\TownHallPublicMarketBundle\Domain\Event;

use Pixel\TownHallPublicMarketBundle\Entity\PublicMarket;
use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;

class PublicMarketModifiedEvent extends DomainEvent
{
    use PublicMarketEventTrait;

    private PublicMarket $publicMarket;

    /**
     * @var array<mixed>
     */
    private array $payload;

    /**
     * @param array<mixed> $payload
     */
    public function __construct(PublicMarket $publicMarket, array $payload)
    {
        parent::__construct();
        $this->publicMarket = $publicMarket;
        $this->payload = $payload;
    }

    public function getEventType(): string
    {
        return 'modified';
    }

    public function getEventPayload(): ?array
    {
        return $this->payload;
    }
}

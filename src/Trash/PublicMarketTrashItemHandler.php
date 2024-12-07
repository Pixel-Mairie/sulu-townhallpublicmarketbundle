<?php

declare(strict_types=1);

namespace Pixel\TownHallPublicMarketBundle\Trash;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\TownHallPublicMarketBundle\Admin\PublicMarketAdmin;
use Pixel\TownHallPublicMarketBundle\Domain\Event\PublicMarketRestoredEvent;
use Pixel\TownHallPublicMarketBundle\Entity\PublicMarket;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\RouteBundle\Entity\Route;
use Sulu\Bundle\TrashBundle\Application\DoctrineRestoreHelper\DoctrineRestoreHelperInterface;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfiguration;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfigurationProviderInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\RestoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\StoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Domain\Model\TrashItemInterface;
use Sulu\Bundle\TrashBundle\Domain\Repository\TrashItemRepositoryInterface;

class PublicMarketTrashItemHandler implements StoreTrashItemHandlerInterface, RestoreTrashItemHandlerInterface, RestoreConfigurationProviderInterface
{
    private TrashItemRepositoryInterface $trashItemRepository;

    private EntityManagerInterface $entityManager;

    private DoctrineRestoreHelperInterface $doctrineRestoreHelper;

    private DomainEventCollectorInterface $domainEventCollector;

    public function __construct(
        TrashItemRepositoryInterface $trashItemRepository,
        EntityManagerInterface $entityManager,
        DoctrineRestoreHelperInterface $doctrineRestoreHelper,
        DomainEventCollectorInterface $domainEventCollector
    ) {
        $this->trashItemRepository = $trashItemRepository;
        $this->entityManager = $entityManager;
        $this->doctrineRestoreHelper = $doctrineRestoreHelper;
        $this->domainEventCollector = $domainEventCollector;
    }

    public static function getResourceKey(): string
    {
        return PublicMarket::RESOURCE_KEY;
    }

    public function store(object $resource, array $options = []): TrashItemInterface
    {
        $status = $resource->getStatus();

        $data = [
            'title' => $resource->getTitle(),
            'statusId' => $status->getId(),
            'slug' => $resource->getRoutePath(),
            'description' => $resource->getDescription(),
            'documents' => $resource->getDocuments(),
            'isActive' => $resource->isActive(),
            'publishedAt' => $resource->getPublishedAt(),
            'seo' => $resource->getSeo(),
        ];

        return $this->trashItemRepository->create(
            PublicMarket::RESOURCE_KEY,
            (string) $resource->getId(),
            $resource->getTitle(),
            $data,
            null,
            $options,
            PublicMarket::SECURITY_CONTEXT,
            null,
            null
        );
    }

    public function restore(TrashItemInterface $trashItem, array $restoreFormData = []): object
    {
        $data = $trashItem->getRestoreData();
        $publicMarketId = (int) $trashItem->getResourceId();

        $publicMarket = new PublicMarket();
        $publicMarket->setTitle($data['title']);
        $publicMarket->setStatus($this->entityManager->find(CategoryInterface::class, $data['statusId']));
        $publicMarket->setRoutePath($data['slug']);
        $publicMarket->setDescription($data['description']);
        $publicMarket->setDocuments($data['documents']);
        $publicMarket->setIsActive($data['isActive']);
        $publicMarket->setPublishedAt($data['publishedAt'] ? new \DateTimeImmutable($data['publishedAt']['date']) : new \DateTimeImmutable());
        $publicMarket->setSeo($data['seo']);
        $this->domainEventCollector->collect(
            new PublicMarketRestoredEvent($publicMarket, $data)
        );

        $this->doctrineRestoreHelper->persistAndFlushWithId($publicMarket, $publicMarketId);

        return $publicMarket;
    }

    private function createRoute(EntityManagerInterface $manager, int $id, string $slug, string $class): void
    {
        $route = new Route();
        $route->setPath($slug);
        $route->setLocale('fr');
        $route->setEntityClass($class);
        $route->setEntityId((string) $id);
        $route->setHistory(false);
        $route->setCreated(new \DateTime());
        $route->setChanged(new \DateTime());
        $manager->persist($route);
    }

    public function getConfiguration(): RestoreConfiguration
    {
        return new RestoreConfiguration(null, PublicMarketAdmin::EDIT_FORM_VIEW, [
            'id' => 'id',
        ]);
    }
}

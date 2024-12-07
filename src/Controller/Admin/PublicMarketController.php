<?php

namespace Pixel\TownHallPublicMarketBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\ViewHandlerInterface;
use HandcraftedInTheAlps\RestRoutingBundle\Controller\Annotations\RouteResource;
use HandcraftedInTheAlps\RestRoutingBundle\Routing\ClassResourceInterface;
use Pixel\TownHallPublicMarketBundle\Common\DoctrineListRepresentationFactory;
use Pixel\TownHallPublicMarketBundle\Domain\Event\PublicMarketCreatedEvent;
use Pixel\TownHallPublicMarketBundle\Domain\Event\PublicMarketModifiedEvent;
use Pixel\TownHallPublicMarketBundle\Domain\Event\PublicMarketRemovedEvent;
use Pixel\TownHallPublicMarketBundle\Entity\PublicMarket;
use Pixel\TownHallPublicMarketBundle\Repository\PublicMarketRepository;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\CategoryBundle\Category\CategoryManagerInterface;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;
use Sulu\Bundle\RouteBundle\Manager\RouteManagerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashManager\TrashManagerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\RestException;
use Sulu\Component\Rest\RequestParametersTrait;
use Sulu\Component\Security\SecuredControllerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @RouteResource("public-market")
 */
class PublicMarketController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    use RequestParametersTrait;

    private DoctrineListRepresentationFactory $doctrineListRepresentationFactory;

    private EntityManagerInterface $entityManager;

    private PublicMarketRepository $repository;

    private CategoryManagerInterface $categoryManager;

    private RouteManagerInterface $routeManager;

    private RouteRepositoryInterface $routeRepository;

    private WebspaceManagerInterface $webspaceManager;

    private DomainEventCollectorInterface $domainEventCollector;

    private TrashManagerInterface $trashManager;

    public function __construct(
        DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        EntityManagerInterface $entityManager,
        PublicMarketRepository $repository,
        CategoryManagerInterface $categoryManager,
        RouteManagerInterface $routeManager,
        RouteRepositoryInterface $routeRepository,
        WebspaceManagerInterface $webspaceManager,
        DomainEventCollectorInterface $domainEventCollector,
        TrashManagerInterface $trashManager,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        $this->doctrineListRepresentationFactory = $doctrineListRepresentationFactory;
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->categoryManager = $categoryManager;
        $this->routeManager = $routeManager;
        $this->routeRepository = $routeRepository;
        $this->webspaceManager = $webspaceManager;
        $this->domainEventCollector = $domainEventCollector;
        $this->trashManager = $trashManager;
        parent::__construct($viewHandler, $tokenStorage);
    }

    public function cgetAction(Request $request): Response
    {
        $locale = $request->query->get('locale');
        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            PublicMarket::RESOURCE_KEY,
            [],
            [
                'locale' => $locale,
            ]
        );
        return $this->handleView($this->view($listRepresentation));
    }

    public function create(Request $request): PublicMarket
    {
        return $this->repository->create((string) $this->getLocale($request));
    }

    public function load(int $id, Request $request, string $defaultLocale = null): ?PublicMarket
    {
        return $this->repository->findById($id, ($defaultLocale) ? $defaultLocale : (string) $this->getLocale($request));
    }

    public function save(PublicMarket $publicMarket): void
    {
        $this->repository->save($publicMarket);
    }

    public function getAction(int $id, Request $request): Response
    {
        $publicMarket = $this->load($id, $request);
        if (! $publicMarket) {
            throw new NotFoundHttpException();
        }

        if ($publicMarket->getTitle() === null && $publicMarket->getDefaultLocale()) {
            $request->setMethod($publicMarket->getDefaultLocale());
            $publicMarket = $this->load($id, $request, $publicMarket->getDefaultLocale());
        }
        return $this->handleView($this->view($publicMarket));
    }

    public function putAction(Request $request, int $id): Response
    {
        $publicMarket = $this->load($id, $request);
        if (! $publicMarket) {
            throw new NotFoundHttpException();
        }

        $data = $request->request->all();
        $this->mapDataToEntity($data, $publicMarket);
        $this->updateRoutesForEntity($publicMarket, $request->query->get('locale'));
        $this->domainEventCollector->collect(
            new PublicMarketModifiedEvent($publicMarket, $data)
        );
        $this->entityManager->flush();
        $this->save($publicMarket);
        return $this->handleView($this->view($publicMarket));
    }

    /**
     * @param array<mixed> $data
     * @throws \Sulu\Bundle\CategoryBundle\Exception\CategoryIdNotFoundException
     */
    public function mapDataToEntity(array $data, PublicMarket $entity): void
    {
        $documents = $data['documents'] ?? null;
        $isActive = $data['isActive'] ?? null;
        $publishedAt = $data['publishedAt'] ?? null;
        $seo = (isset($data['ext']['seo'])) ? $data['ext']['seo'] : null;
        $status = (isset($data['status']['id'])) ? $data['status']['id'] : $data['status'];

        $entity->setTitle($data['title']);
        $entity->setStatus($this->categoryManager->findById($status));
        $entity->setRoutePath($data['routePath']);
        $entity->setDescription($data['description']);
        $entity->setDocuments($documents);
        $entity->setIsActive($isActive);
        $entity->setPublishedAt($publishedAt ? new \DateTimeImmutable($publishedAt) : new \DateTimeImmutable());
        $entity->setSeo($seo);
    }

    protected function updateRoutesForEntity(PublicMarket $entity, string $locale): void
    {
        $this->routeManager->createOrUpdateByAttributes(
            PublicMarket::class,
            (string) $entity->getId(),
            $locale,
            $entity->getRoutePath()
        );
    }

    public function postAction(Request $request): Response
    {
        $publicMarket = $this->create($request);
        $data = $request->request->all();
        $this->mapDataToEntity($data, $publicMarket);
        $this->save($publicMarket);
        $this->updateRoutesForEntity($publicMarket, $request->query->get('locale'));
        $this->domainEventCollector->collect(
            new PublicMarketCreatedEvent($publicMarket, $data)
        );
        $this->entityManager->flush();
        return $this->handleView($this->view($publicMarket, 201));
    }

    public function deleteAction(int $id): Response
    {
        /** @var PublicMarket $publicMarket */
        $publicMarket = $this->entityManager->getRepository(PublicMarket::class)->find($id);
        $publicMarketTitle = $publicMarket->getTitle();
        if ($publicMarket) {
            $this->trashManager->store(PublicMarket::RESOURCE_KEY, $publicMarket);
            $this->entityManager->remove($publicMarket);
            $this->removeRoutesForEntity($publicMarket);
            $this->domainEventCollector->collect(
                new PublicMarketRemovedEvent($id, $publicMarketTitle)
            );
        }
        $this->entityManager->flush();
        return $this->handleView($this->view(null, 204));
    }

    protected function removeRoutesForEntity(PublicMarket $entity): void
    {
        // remove route for all locales of the application because event entity is not localized
        foreach ($this->webspaceManager->getAllLocales() as $locale) {
            $routes = $this->routeRepository->findAllByEntity(
                PublicMarket::class,
                (string) $entity->getId(),
                $locale,
            );

            foreach ($routes as $route) {
                $this->routeRepository->remove($route);
            }
        }
    }

    /**
     * @Rest\Post("/public-markets/{id}")
     */
    public function postTriggerAction(int $id, Request $request): Response
    {
        $action = $this->getRequestParameter($request, 'action', true);
        $locale = $this->getRequestParameter($request, 'locale', true);

        try {
            switch ($action) {
                case 'enable':
                    $item = $this->entityManager->getRepository(PublicMarket::class)->find($id);
                    $item->setLocale($locale);
                    $item->setIsActive(true);
                    $this->entityManager->persist($item);
                    $this->entityManager->flush();
                    break;
                case 'disable':
                    $item = $this->entityManager->getRepository(PublicMarket::class)->find($id);
                    $item->setLocale($locale);
                    $item->setIsActive(false);
                    $this->entityManager->persist($item);
                    $this->entityManager->flush();
                    break;
                default:
                    throw new BadRequestHttpException(sprintf('Unknown action "%s".', $action));
            }
        } catch (RestException $exc) {
            $view = $this->view($exc->toArray(), 400);
            return $this->handleView($view);
        }

        return $this->handleView($this->view($item));
    }

    public function getSecurityContext()
    {
        return PublicMarket::SECURITY_CONTEXT;
    }
}

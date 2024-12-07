<?php

namespace Pixel\TownHallPublicMarketBundle\Preview;

use Pixel\TownHallPublicMarketBundle\Entity\PublicMarket;
use Pixel\TownHallPublicMarketBundle\Repository\PublicMarketRepository;
use Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderInterface;

class PublicMarketObjectProvider implements PreviewObjectProviderInterface
{
    private PublicMarketRepository $publicMarketRepository;

    public function __construct(PublicMarketRepository $publicMarketRepository)
    {
        $this->publicMarketRepository = $publicMarketRepository;
    }

    public function getObject($id, $locale): PublicMarket
    {
        return $this->publicMarketRepository->find((int) $id);
    }

    /**
     * @param PublicMarket $object
     * @return string
     */
    public function getId($object)
    {
        return (string) $object->getId();
    }

    /**
     * @param PublicMarket $object
     * @param array<mixed> $data
     */
    public function setValues($object, $locale, array $data): void
    {
        $documents = $data['documents'] ?? null;
        $isActive = $data['isActive'] ?? null;
        $publishedAt = $data['publishedAt'] ?? null;
        $seo = (isset($data['ext']['seo'])) ? $data['ext']['seo'] : null;

        $object->setTitle($data['title']);
        $object->setRoutePath($data['routePath']);
        $object->setDescription($data['description']);
        $object->setDocuments($documents);
        $object->setIsActive($isActive);
        $object->setPublishedAt($publishedAt ? new \DateTimeImmutable($publishedAt) : new \DateTimeImmutable());
        $object->setSeo($seo);
    }

    /**
     * @param object $object
     * @param string $locale
     * @param array<mixed> $context
     * @return mixed
     */
    public function setContext($object, $locale, array $context)
    {
        if (\array_key_exists('template', $context)) {
            $object->setStructureType($context['template']);
        }
        return $object;
    }

    /**
     * @param PublicMarket $object
     * @return string
     */
    public function serialize($object)
    {
        if (! $object->getTitle()) {
            $object->setTitle('');
        }
        if (! $object->getDescription()) {
            $object->setDescription('');
        }

        return serialize($object);
    }

    /**
     * @return mixed
     */
    public function deserialize($serializedObject, $objectClass)
    {
        return unserialize($serializedObject);
    }

    public function getSecurityContext($id, $locale): ?string
    {
        return PublicMarket::SECURITY_CONTEXT;
    }
}

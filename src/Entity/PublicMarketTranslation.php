<?php

namespace Pixel\TownHallPublicMarketBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="townhall_public_market_translations")
 * @ORM\Entity(repositoryClass="Pixel\TownHallPublicMarketBundle\Repository\PublicMarketRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class PublicMarketTranslation implements AuditableInterface
{
    use AuditableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="Pixel\TownHallPublicMarketBundle\Entity\PublicMarket", inversedBy="translations")
     * @ORM\JoinColumn(nullable=true)
     */
    private PublicMarket $publicMarket;

    /**
     * @ORM\Column(type="string", length=5)
     * @Serializer\Expose()
     */
    private string $locale;

    /**
     * @ORM\Column(type="string")
     * @Serializer\Expose()
     */
    private string $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Expose()
     */
    private string $routePath;

    /**
     * @ORM\Column(type="text")
     * @Serializer\Expose()
     */
    private string $description;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Serializer\Expose()
     */
    private ?bool $isActive;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Serializer\Expose()
     */
    private ?\DateTimeImmutable $publishedAt;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Serializer\Expose()
     * @var array<mixed>
     */
    private ?array $seo = null;

    public function __construct(PublicMarket $publicMarket, string $locale)
    {
        $this->publicMarket = $publicMarket;
        $this->locale = $locale;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicMarket(): PublicMarket
    {
        return $this->publicMarket;
    }

    public function setPublicMarket(PublicMarket $publicMarket): void
    {
        $this->publicMarket = $publicMarket;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getRoutePath(): string
    {
        return $this->routePath;
    }

    public function setRoutePath(string $routePath): void
    {
        $this->routePath = $routePath;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): void
    {
        $this->isActive = $isActive;
        if ($isActive === true) {
            $this->setPublishedAt(new \DateTimeImmutable());
        } else {
            $this->setPublishedAt(null);
        }
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    /**
     * @return array<mixed>|null
     */
    public function getSeo(): ?array
    {
        return $this->seo;
    }

    /**
     * @param array<mixed>|null $seo
     */
    public function setSeo(?array $seo): void
    {
        $this->seo = $seo;
    }
}

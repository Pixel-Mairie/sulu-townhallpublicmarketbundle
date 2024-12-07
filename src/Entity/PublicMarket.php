<?php

namespace Pixel\TownHallPublicMarketBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\CategoryBundle\Entity\Category;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="townhall_public_market")
 * @ORM\Entity(repositoryClass="Pixel\TownHallPublicMarketBundle\Repository\PublicMarketRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class PublicMarket
{
    public const RESOURCE_KEY = "publics_markets";

    public const FORM_KEY = "public_market_details";

    public const LIST_KEY = "publics_markets";

    public const SECURITY_CONTEXT = "townhall.publics_markets";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=CategoryInterface::class)
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Expose()
     */
    private CategoryInterface $status;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Serializer\Expose()
     * @var array<mixed>|null
     */
    private ?array $documents = null;

    /**
     * @var Collection<string, PublicMarketTranslation>
     * @ORM\OneToMany(targetEntity="Pixel\TownHallPublicMarketBundle\Entity\PublicMarketTranslation", mappedBy="publicMarket", cascade={"ALL"}, indexBy="locale")
     * @Serializer\Exclude()
     */
    private $translations;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $defaultLocale;

    private string $locale = "fr";

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): CategoryInterface
    {
        return $this->status;
    }

    public function setStatus(CategoryInterface $status): void
    {
        $this->status = $status;
    }

    /**
     * @return array<mixed>|null
     */
    public function getDocuments(): ?array
    {
        return $this->documents;
    }

    /**
     * @param array<mixed>|null $documents
     */
    public function setDocuments(?array $documents): void
    {
        $this->documents = $documents;
    }

    /**
     * @return array<string, PublicMarketTranslation>
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    protected function getTranslation(string $locale = 'fr'): ?PublicMarketTranslation
    {
        if (! $this->translations->containsKey($locale)) {
            return null;
        }
        return $this->translations->get($locale);
    }

    protected function createTranslation(string $locale): PublicMarketTranslation
    {
        $translation = new PublicMarketTranslation($this, $locale);
        $this->translations->set($locale, $translation);
        return $translation;
    }

    public function getDefaultLocale(): ?string
    {
        return $this->defaultLocale;
    }

    public function setDefaultLocale(?string $defaultLocale): void
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="title")
     */
    public function getTitle(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            return null;
        }
        return $translation->getTitle();
    }

    public function setTitle(string $title): self
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setTitle($title);
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="route")
     */
    public function getRoutePath(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            return null;
        }
        return $translation->getRoutePath();
    }

    public function setRoutePath(string $routePath): self
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setRoutePath($routePath);
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="description")
     */
    public function getDescription(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            return null;
        }
        return $translation->getDescription();
    }

    public function setDescription(string $description): self
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setDescription($description);
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="is_active")
     */
    public function isActive(): ?bool
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            return null;
        }
        return $translation->isActive();
    }

    public function setIsActive(?bool $isActive): self
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setIsActive($isActive);
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="published_at")
     */
    public function getPublishedAt(): ?\DateTimeImmutable
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            return null;
        }
        return $translation->getPublishedAt();
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): self
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setPublishedAt($publishedAt);
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="seo")
     * @return array<mixed>|null
     */
    public function getSeo(): ?array
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            return null;
        }
        return $translation->getSeo();
    }

    /**
     * @param array<mixed>|null $seo
     * @return $this
     */
    public function setSeo(?array $seo): self
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setSeo($seo);
        return $this;
    }

    /**
     * @return array<string, array<string>>
     */
    protected function emptySeo(): array
    {
        return [
            "seo" => [
                "title" => "",
                "description" => "",
                "keywords" => "",
                "canonicalUrl" => "",
                "noIndex" => "",
                "noFollow" => "",
                "hideinSitemap" => "",
            ],
        ];
    }

    /**
     * @Serializer\VirtualProperty(name="ext")
     * @return array<mixed>|null
     */
    public function getExt(): ?array
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            return null;
        }
        return ($translation->getSeo()) ? [
            'seo' => $translation->getSeo(),
        ] : $this->emptySeo();
    }
}

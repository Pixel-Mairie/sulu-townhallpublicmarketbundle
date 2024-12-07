<?php

namespace Pixel\TownHallPublicMarketBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Pixel\TownHallPublicMarketBundle\Entity\PublicMarket;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryTrait;

class PublicMarketRepository extends EntityRepository implements DataProviderRepositoryInterface
{
    use DataProviderRepositoryTrait;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, new ClassMetadata(PublicMarket::class));
    }

    public function create(string $locale): PublicMarket
    {
        $publicMarket = new PublicMarket();
        $publicMarket->setDefaultLocale($locale);
        $publicMarket->setLocale($locale);
        return $publicMarket;
    }

    public function save(PublicMarket $publicMarket): void
    {
        $this->getEntityManager()->persist($publicMarket);
        $this->getEntityManager()->flush();
    }

    public function findById(int $id, string $locale): ?PublicMarket
    {
        $publicMarket = $this->find($id);
        if (! $publicMarket) {
            return null;
        }
        $publicMarket->setLocale($locale);
        return $publicMarket;
    }

    /**
     * @param string $alias
     * @param string $locale
     */
    public function appendJoins(QueryBuilder $queryBuilder, $alias, $locale): void
    {
    }

    /**
     * @param array<mixed> $filters
     * @param array<mixed> $options
     * @return array|object[]
     */
    public function findByFilters($filters, $page, $pageSize, $limit, $locale, $options = []): array
    {
        $entities = $this->getActivePublicMarket($filters, $locale, $page, $pageSize, $limit, $options);

        return \array_map(
            function (PublicMarket $entity) use ($locale) {
                return $entity->setLocale($locale);
            },
            $entities
        );
    }

    /**
     * @param array<mixed> $filters
     * @param array<mixed> $options
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function hasNextPage(array $filters, ?int $page, ?string $pageSize, ?int $limit, string $locale, array $options = []): bool
    {
        $pageCurrent = (key_exists('page', $options)) ? (int) $options['page'] : 0;

        $totalMarchesPublics = $this->createQueryBuilder('pm')
            ->select('count(pm.id)')
            ->leftJoin('pm.translations', 'translation')
            ->where('translation.isActive = 1')
            ->andWhere('translation.locale = :locale')->setParameter('locale', $locale)
            ->getQuery()
            ->getSingleScalarResult();

        if ((int) ($limit * $pageCurrent) + $limit < (int) $totalMarchesPublics) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array<mixed> $filters
     * @param array<mixed> $options
     * @return array<PublicMarket>
     */
    public function getActivePublicMarket(array $filters, string $locale, ?int $page, ?int $pageSize, ?int $limit, array $options): array
    {
        $pageCurrent = (key_exists('page', $options)) ? (int) $options['page'] : 0;

        $query = $this->createQueryBuilder('pm')
            ->leftJoin('pm.translations', 'translation')
            ->where('translation.isActive = 1')
            ->andWhere('translation.locale = :locale')->setParameter('locale', $locale)
            ->setMaxResults($limit)
            ->setFirstResult($pageCurrent * $limit);

        if (! empty($filters['categories'])) {
            $i = 0;
            if ($filters['categoryOperator'] === "and") {
                $andWhere = "";
                foreach ($filters['categories'] as $category) {
                    if ($i === 0) {
                        $andWhere .= "pm.status = :category" . $i;
                    } else {
                        $andWhere .= " AND pm.status = :category" . $i;
                    }
                    $query->setParameter("category" . $i, $category);
                    $i++;
                }
                $query->andWhere($andWhere);
            } elseif ($filters['categoryOperator'] === "or") {
                $orWhere = "";
                foreach ($filters['categories'] as $category) {
                    if ($i === 0) {
                        $orWhere .= "pm.status = :category" . $i;
                    } else {
                        $orWhere .= " OR pm.status = :category" . $i;
                    }
                    $query->setParameter("category" . $i, $category);
                    $i++;
                }
                $query->andWhere($orWhere);
            }
        }
        if (isset($filters['sortBy'])) {
            $query->orderBy($filters['sortBy'], $filters['sortMethod']);
        }
        $publicMarkets = $query->getQuery()->getResult();
        if (! $publicMarkets) {
            return [];
        }
        return $publicMarkets;
    }

    protected function appendSortByJoins(QueryBuilder $queryBuilder, string $alias, string $locale): void
    {
        $queryBuilder->innerJoin($alias . ".translations", "translation", Join::WITH, "translation.locale = :locale");
        $queryBuilder->setParameter("locale", $locale);
    }
}

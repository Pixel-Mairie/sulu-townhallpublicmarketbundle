<?php

declare(strict_types=1);

namespace Pixel\TownHallPublicMarketBundle\Content;

use Sulu\Component\Content\Compat\PropertyParameter;
use Sulu\Component\Serializer\ArraySerializerInterface;
use Sulu\Component\SmartContent\ItemInterface;
use Sulu\Component\SmartContent\Orm\BaseDataProvider;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class PublicMarketDataProvider extends BaseDataProvider
{
    private RequestStack $requestStack;

    public function __construct(DataProviderRepositoryInterface $repository, ArraySerializerInterface $serializer, RequestStack $requestStack)
    {
        parent::__construct($repository, $serializer);
        $this->requestStack = $requestStack;
    }

    public function getConfiguration()
    {
        if (! $this->configuration) {
            $this->configuration = self::createConfigurationBuilder()
                ->enableLimit()
                ->enablePagination()
                ->enablePresentAs()
                ->enableCategories()
                ->enableSorting([
                    [
                        'column' => "translation.title",
                        'title' => "townhall.title",
                    ],
                    [
                        'column' => "translation.publishedAt",
                        'title' => "townhall.publishedAt",
                    ],
                ])
                ->getConfiguration();
        }
        return $this->configuration;
    }

    /**
     * Decorates result as data item.
     *
     * @param array<mixed> $data
     *
     * @return ItemInterface[]
     */
    protected function decorateDataItems(array $data)
    {
        return array_map(
            function ($item) {
                return new PublicMarketDataItem($item);
            },
            $data
        );
    }

    /**
     * Returns additional options for query creation.
     *
     * @param PropertyParameter[] $propertyParameter
     * @param array<mixed> $options
     *
     * @return array<mixed>
     */
    protected function getOptions(array $propertyParameter, array $options = [])
    {
        $request = $this->requestStack->getCurrentRequest();

        $result = [
            'type' => $request->get('type'),
        ];

        return array_filter($result);
    }
}

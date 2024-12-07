<?php

namespace Pixel\TownHallPublicMarketBundle\DependencyInjection;

use Pixel\TownHallPublicMarketBundle\Admin\PublicMarketAdmin;
use Pixel\TownHallPublicMarketBundle\Entity\PublicMarket;
use Sulu\Bundle\PersistenceBundle\DependencyInjection\PersistenceExtensionTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;

class TownHallPublicMarketExtension extends Extension implements PrependExtensionInterface
{
    use PersistenceExtensionTrait;

    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasExtension("sulu_admin")) {
            $container->prependExtensionConfig(
                "sulu_admin",
                [
                    'forms' => [
                        'directories' => [
                            __DIR__ . "/../Resources/config/forms",
                        ],
                    ],
                    'lists' => [
                        'directories' => [
                            __DIR__ . "/../Resources/config/lists",
                        ],
                    ],
                    'resources' => [
                        'publics_markets' => [
                            'routes' => [
                                'detail' => 'townhall.get_public-market',
                                'list' => 'townhall.get_public-markets',
                            ],
                        ],
                    ],
                    'field_type_options' => [
                        'selection' => [
                            'public_market_selection' => [
                                'default_type' => "list_overlay",
                                'resource_key' => PublicMarket::RESOURCE_KEY,
                                'view' => [
                                    'name' => PublicMarketAdmin::EDIT_FORM_VIEW,
                                    'result_to_view' => [
                                        'id' => 'id',
                                    ],
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => "table",
                                        'list_key' => PublicMarket::LIST_KEY,
                                        'display_properties' => ['title'],
                                        'icon' => "fa-solid fa-helmet-safety",
                                        'label' => "townhall.publics_markets",
                                        'overlay_title' => "townhall.public_markets.list",
                                    ],
                                ],
                            ],
                        ],
                        'single_selection' => [
                            'single_public_market_selection' => [
                                'default_type' => "list_overlay",
                                'resource_key' => PublicMarket::RESOURCE_KEY,
                                'view' => [
                                    'name' => PublicMarketAdmin::EDIT_FORM_VIEW,
                                    'result_to_view' => [
                                        'id' => 'id',
                                    ],
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => PublicMarket::LIST_KEY,
                                        'display_properties' => ['title'],
                                        'icon' => "fa-helmet-safety",
                                        'empty_text' => "townhall.public_market.emptyText",
                                        'overlay_title' => "townhall.public_market.list",
                                    ],
                                    'auto_complete' => [
                                        'display_property' => 'title',
                                        'search_properties' => ['title'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            );
        }
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loaderYaml = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load("services.xml");
        $loaderYaml->load("services.yaml");
    }
}

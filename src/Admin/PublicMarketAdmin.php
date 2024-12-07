<?php

namespace Pixel\TownHallPublicMarketBundle\Admin;

use Pixel\TownHallPublicMarketBundle\Entity\PublicMarket;
use Sulu\Bundle\ActivityBundle\Infrastructure\Sulu\Admin\View\ActivityViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\TogglerToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\ReferenceBundle\Infrastructure\Sulu\Admin\View\ReferenceViewBuilderFactoryInterface;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class PublicMarketAdmin extends Admin
{
    public const LIST_VIEW = "townhall.public_market.list";

    public const ADD_FORM_VIEW = "townhall.public_market.add_form";

    public const ADD_FORM_DETAILS_VIEW = "townhall.public_market.add_form_details";

    public const EDIT_FORM_VIEW = "townhall.public_market.edit_form";

    public const EDIT_FORM_DETAILS_VIEW = "townhall.public_market.edit_form_details";

    public const EDIT_FORM_SEO_VIEW = "townhall.public_market.seo.edit_form";

    private ViewBuilderFactoryInterface $viewBuilderFactory;

    private SecurityCheckerInterface $securityChecker;

    private WebspaceManagerInterface $webspaceManager;

    private ActivityViewBuilderFactoryInterface $activityViewBuilderFactory;

    private ReferenceViewBuilderFactoryInterface $referenceViewBuilderFactory;

    public function __construct(
        ViewBuilderFactoryInterface $viewBuilderFactory,
        SecurityCheckerInterface $securityChecker,
        WebspaceManagerInterface $webspaceManager,
        ActivityViewBuilderFactoryInterface $activityViewBuilderFactory,
        ReferenceViewBuilderFactoryInterface $referenceViewBuilderFactory
    ) {
        $this->viewBuilderFactory = $viewBuilderFactory;
        $this->securityChecker = $securityChecker;
        $this->webspaceManager = $webspaceManager;
        $this->activityViewBuilderFactory = $activityViewBuilderFactory;
        $this->referenceViewBuilderFactory = $referenceViewBuilderFactory;
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(PublicMarket::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $navigationItem = new NavigationItem("townhall.publics_markets");
            $navigationItem->setView(static::LIST_VIEW);
            $navigationItemCollection->get('townhall')->addChild($navigationItem);
        }
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        $locales = $this->webspaceManager->getAllLocales();
        $formToolbarActions = [];
        $listToolbarActions = [];

        if ($this->securityChecker->hasPermission(PublicMarket::SECURITY_CONTEXT, PermissionTypes::ADD)) {
            $listToolbarActions[] = new ToolbarAction("sulu_admin.add");
        }

        if ($this->securityChecker->hasPermission(PublicMarket::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $formToolbarActions[] = new ToolbarAction("sulu_admin.save");
            $formToolbarActions[] = new TogglerToolbarAction(
                "townhall.isActive",
                "isActive",
                "enable",
                "disable"
            );
        }

        if ($this->securityChecker->hasPermission(PublicMarket::SECURITY_CONTEXT, PermissionTypes::DELETE)) {
            $formToolbarActions[] = new ToolbarAction("sulu_admin.delete");
            $listToolbarActions[] = new ToolbarAction("sulu_admin.delete");
        }

        if ($this->securityChecker->hasPermission(PublicMarket::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $listToolbarActions[] = new ToolbarAction("sulu_admin.export");
        }

        if ($this->securityChecker->hasPermission(PublicMarket::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $viewCollection->add(
                $this->viewBuilderFactory->createListViewBuilder(static::LIST_VIEW, "/publics-markets/:locale")
                    ->setResourceKey(PublicMarket::RESOURCE_KEY)
                    ->setListKey(PublicMarket::LIST_KEY)
                    ->setTitle("townhall.publics_markets")
                    ->addListAdapters(['table'])
                    ->addLocales($locales)
                    ->setDefaultLocale($locales[0])
                    ->setAddView(static::ADD_FORM_VIEW)
                    ->setEditView(static::EDIT_FORM_VIEW)
                    ->addToolbarActions($listToolbarActions)
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::ADD_FORM_VIEW, "/publics-markets/:locale/add")
                    ->setResourceKey(PublicMarket::RESOURCE_KEY)
                    ->addLocales($locales)
                    ->setBackView(static::LIST_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::ADD_FORM_DETAILS_VIEW, "/details")
                    ->setResourceKey(PublicMarket::RESOURCE_KEY)
                    ->setFormKey(PublicMarket::FORM_KEY)
                    ->setTabTitle("sulu_admin.details")
                    ->setEditView(static::EDIT_FORM_VIEW)
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::ADD_FORM_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::EDIT_FORM_VIEW, "/publics-markets/:locale/:id")
                    ->setResourceKey(PublicMarket::RESOURCE_KEY)
                    ->addLocales($locales)
                    ->setBackView(static::LIST_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createPreviewFormViewBuilder(static::EDIT_FORM_DETAILS_VIEW, "/details")
                    ->setResourceKey(PublicMarket::RESOURCE_KEY)
                    ->setFormKey(PublicMarket::FORM_KEY)
                    ->setTabTitle("sulu_admin.details")
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::EDIT_FORM_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::EDIT_FORM_SEO_VIEW, "/seo")
                    ->setResourceKey(PublicMarket::RESOURCE_KEY)
                    ->setFormKey("seo")
                    ->setTabTitle("sulu_page.seo")
                    ->addToolbarActions($formToolbarActions)
                    ->setTitleVisible(true)
                    ->setTabOrder(2048)
                    ->setParent(static::EDIT_FORM_VIEW)
            );

            if ($this->activityViewBuilderFactory->hasActivityListPermission()) {
                $viewCollection->add(
                    $this->activityViewBuilderFactory->createActivityListViewBuilder(static::EDIT_FORM_VIEW . ".activity", "/activity", PublicMarket::RESOURCE_KEY)
                        ->setParent(static::EDIT_FORM_VIEW)
                );
            }

            if ($this->referenceViewBuilderFactory->hasReferenceListPermission()) {
                $viewCollection->add(
                    $this->referenceViewBuilderFactory->createReferenceListViewBuilder(static::EDIT_FORM_VIEW . ".insights.reference", "/references", PublicMarket::RESOURCE_KEY)
                        ->setParent(static::EDIT_FORM_VIEW)
                );
            }
        }
    }

    /**
     * @return mixed[]
     */
    public function getSecurityContexts(): array
    {
        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Public market' => [
                    PublicMarket::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                    ],
                ],
            ],
        ];
    }
}

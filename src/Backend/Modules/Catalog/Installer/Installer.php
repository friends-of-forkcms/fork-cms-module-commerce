<?php

namespace Backend\Modules\Catalog\Installer;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Modules\Catalog\Domain\Brand\Brand;
use Backend\Modules\Catalog\Domain\Category\Category;

use Backend\Core\Engine\Model;
use Backend\Core\Installer\ModuleInstaller;
use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\ProductSpecial\ProductSpecial;
use Backend\Modules\Catalog\Domain\Specification\Specification;
use Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValue;
use Backend\Modules\Catalog\Domain\StockStatus\StockStatus;
use Backend\Modules\Catalog\Domain\Vat\Vat;
use Common\ModuleExtraType;

/**
 * Installer for the Catalog module
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Jacob van Dam (Jacob van Dam ICT) <j.vandam@jvdict.nl>
 */
class Installer extends ModuleInstaller
{
    public function install()
    {
        $this->configureEntities();

        // add 'catalog' as a module
        $this->addModule('Catalog');

        // import locale
        $this->importLocale(dirname(__FILE__) . '/Data/locale.xml');

        // general settings
        $this->setSetting('Catalog', 'allow_comments', true);
        $this->setSetting('Catalog', 'requires_akismet', true);
        $this->setSetting('Catalog', 'spamfilter', false);
        $this->setSetting('Catalog', 'moderation', true);
        $this->setSetting('Catalog', 'overview_num_items', 10);
        $this->setSetting('Catalog', 'recent_products_full_num_items', 3);
        $this->setSetting('Catalog', 'allow_multiple_categories', true);

        $this->setSetting('Catalog', 'width1', (int)400);
        $this->setSetting('Catalog', 'height1', (int)300);
        $this->setSetting('Catalog', 'allow_enlargment1', true);
        $this->setSetting('Catalog', 'force_aspect_ratio1', true);

        $this->setSetting('Catalog', 'width2', (int)800);
        $this->setSetting('Catalog', 'height2', (int)600);
        $this->setSetting('Catalog', 'allow_enlargment2', true);
        $this->setSetting('Catalog', 'force_aspect_ratio2', true);

        $this->setSetting('Catalog', 'width3', (int)1600);
        $this->setSetting('Catalog', 'height3', (int)1200);
        $this->setSetting('Catalog', 'allow_enlargment3', true);
        $this->setSetting('Catalog', 'force_aspect_ratio3', true);

        $this->makeSearchable('Catalog');

        // module rights
        $this->setModuleRights(1, 'Catalog');

        // products and index
        $this->setActionRights(1, 'Catalog', 'Index');
        $this->setActionRights(1, 'Catalog', 'Add');
        $this->setActionRights(1, 'Catalog', 'Edit');
        $this->setActionRights(1, 'Catalog', 'Delete');

        // categories
        $this->setActionRights(1, 'Catalog', 'Categories');
        $this->setActionRights(1, 'Catalog', 'AddCategory');
        $this->setActionRights(1, 'Catalog', 'EditCategory');
        $this->setActionRights(1, 'Catalog', 'DeleteCategory');
        $this->setActionRights(1, 'Catalog', 'SequenceCategories');

        // specifications
        $this->setActionRights(1, 'Catalog', 'Specifications');
        $this->setActionRights(1, 'Catalog', 'EditSpecification');
        $this->setActionRights(1, 'Catalog', 'DeleteSpecification');
        $this->setActionRights(1, 'Catalog', 'SequenceSpecifications');

        // orders
        $this->setActionRights(1, 'Catalog', 'Orders');
        $this->setActionRights(1, 'Catalog', 'EditOrder');
        $this->setActionRights(1, 'Catalog', 'DeleteCompleted');
        $this->setActionRights(1, 'Catalog', 'MassOrderAction');

        // settings
        $this->setActionRights(1, 'Catalog', 'Settings');

        // brands
        $this->setActionRights(1, 'Catalog', 'Brands');
        $this->setActionRights(1, 'Catalog', 'AddBrand');
        $this->setActionRights(1, 'Catalog', 'EditBrand');
        $this->setActionRights(1, 'Catalog', 'DeleteBrand');
        $this->setActionRights(1, 'Catalog', 'SequenceBrands');

        // vats
        $this->setActionRights(1, 'Catalog', 'Vats');
        $this->setActionRights(1, 'Catalog', 'AddVat');
        $this->setActionRights(1, 'Catalog', 'EditVat');
        $this->setActionRights(1, 'Catalog', 'DeleteVat');
        $this->setActionRights(1, 'Catalog', 'SequenceVats');

        // stock statuses
        $this->setActionRights(1, 'Catalog', 'StockStatuses');
        $this->setActionRights(1, 'Catalog', 'AddStockStatus');
        $this->setActionRights(1, 'Catalog', 'EditStockStatus');
        $this->setActionRights(1, 'Catalog', 'DeleteStockStatus');

        // add extra's
        $this->insertExtra('Catalog', ModuleExtraType::block(), 'Catalog');
        $this->insertExtra('Catalog', ModuleExtraType::block(), 'Brand', 'Brand');
        $this->insertExtra('Catalog', ModuleExtraType::widget(), 'Categories', 'Categories');
        $this->insertExtra('Catalog', ModuleExtraType::widget(), 'ShoppingCart', 'ShoppingCart');
        $this->insertExtra('Catalog', ModuleExtraType::widget(), 'RecentProducts', 'RecentProducts');
        $this->insertExtra('Catalog', ModuleExtraType::widget(), 'Brands', 'Brands');

        // set navigation
        $navigationModulesId = $this->setNavigation(null, 'Modules');
        $navigationCatalogId = $this->setNavigation($navigationModulesId, 'Catalog');
        $this->setNavigation(
            $navigationCatalogId,
            'Products',
            'catalog/index',
            [
                'catalog/add',
                'catalog/edit',
            ]
        );
        $this->setNavigation(
            $navigationCatalogId,
            'Categories',
            'catalog/categories',
            [
                'catalog/add_category',
                'catalog/edit_category'
            ]
        );
        $this->setNavigation(
            $navigationCatalogId,
            'Specifications',
            'catalog/specifications',
            [
                'catalog/add_specification',
                'catalog/edit_specification'
            ]
        );
        $this->setNavigation(
            $navigationCatalogId,
            'Orders',
            'catalog/orders',
            [
                'catalog/edit_order'
            ]
        );
        $this->setNavigation(
            $navigationCatalogId,
            'Brands',
            'catalog/brands',
            [
                'catalog/add_brand',
                'catalog/edit_brand'
            ]
        );
        $this->setNavigation(
            $navigationCatalogId,
            'Vats',
            'catalog/vats',
            [
                'catalog/add_vat',
                'catalog/edit_vat'
            ]
        );
        $this->setNavigation(
            $navigationCatalogId,
            'StockStatuses',
            'catalog/stock_statuses',
            [
                'catalog/add_stock_status',
                'catalog/edit_stock_status'
            ]
        );

        // settings navigation
        $navigationSettingsId = $this->setNavigation(null, 'Settings');
        $navigationModulesId  = $this->setNavigation($navigationSettingsId, 'Modules');
        $this->setNavigation($navigationModulesId, 'Catalog', 'catalog/settings');
    }

    private function configureEntities(): void
    {
        Model::get('fork.entity.create_schema')->forEntityClass(Category::class);
        Model::get('fork.entity.create_schema')->forEntityClass(Brand::class);
        Model::get('fork.entity.create_schema')->forEntityClass(Vat::class);
        Model::get('fork.entity.create_schema')->forEntityClass(StockStatus::class);
        Model::get('fork.entity.create_schema')->forEntityClass(Product::class);
        Model::get('fork.entity.create_schema')->forEntityClass(ProductSpecial::class);
        Model::get('fork.entity.create_schema')->forEntityClass(Order::class);
        Model::get('fork.entity.create_schema')->forEntityClass(Specification::class);
        Model::get('fork.entity.create_schema')->forEntityClass(SpecificationValue::class);
    }
}

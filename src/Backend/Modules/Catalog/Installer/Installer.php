<?php

namespace Backend\Modules\Catalog\Installer;

use Backend\Core\Engine\Model;
use Backend\Core\Installer\ModuleInstaller;
use Backend\Modules\Catalog\Domain\Brand\Brand;
use Backend\Modules\Catalog\Domain\Cart\Cart;
use Backend\Modules\Catalog\Domain\Cart\CartValue;
use Backend\Modules\Catalog\Domain\Cart\CartValueOption;
use Backend\Modules\Catalog\Domain\Category\Category;
use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Catalog\Domain\OrderHistory\OrderHistory;
use Backend\Modules\Catalog\Domain\OrderProduct\OrderProduct;
use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatus;
use Backend\Modules\Catalog\Domain\OrderVat\OrderVat;
use Backend\Modules\Catalog\Domain\PaymentMethod\PaymentMethod;
use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\ProductOption\ProductOption;
use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue;
use Backend\Modules\Catalog\Domain\ProductSpecial\ProductSpecial;
use Backend\Modules\Catalog\Domain\ShipmentMethod\ShipmentMethod;
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
        $this->setSetting('Catalog', 'overview_num_items', 10);
        $this->setSetting('Catalog', 'filters_show_more_num_items', 5);

        $this->makeSearchable('Catalog');

        // module rights
        $this->setModuleRights(1, 'Catalog');

        // products and index
        $this->setActionRights(1, 'Catalog', 'Index');
        $this->setActionRights(1, 'Catalog', 'Add');
        $this->setActionRights(1, 'Catalog', 'Edit');
        $this->setActionRights(1, 'Catalog', 'Delete');
        $this->setActionRights(1, 'Catalog', 'SequenceProducts');
        $this->setActionRights(1, 'Catalog', 'AutoCompleteProducts');

        // categories
        $this->setActionRights(1, 'Catalog', 'Categories');
        $this->setActionRights(1, 'Catalog', 'AddCategory');
        $this->setActionRights(1, 'Catalog', 'EditCategory');
        $this->setActionRights(1, 'Catalog', 'DeleteCategory');
        $this->setActionRights(1, 'Catalog', 'SequenceCategories');

        // specifications
        $this->setActionRights(1, 'Catalog', 'Specifications');
        $this->setActionRights(1, 'Catalog', 'EditSpecification');
        $this->setActionRights(1, 'Catalog', 'AddSpecification');
        $this->setActionRights(1, 'Catalog', 'DeleteSpecification');
        $this->setActionRights(1, 'Catalog', 'SequenceSpecifications');

        // specification values
        $this->setActionRights(1, 'Catalog', 'EditSpecificationValue');
        $this->setActionRights(1, 'Catalog', 'DeleteSpecificationValue');
        $this->setActionRights(1, 'Catalog', 'SequenceSpecificationValues');
        $this->setActionRights(1, 'Catalog', 'AutoCompleteSpecificationValue');

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

        // order statuses
        $this->setActionRights(1, 'Catalog', 'OrderStatuses');
        $this->setActionRights(1, 'Catalog', 'AddOrderStatus');
        $this->setActionRights(1, 'Catalog', 'EditOrderStatus');
        $this->setActionRights(1, 'Catalog', 'DeleteOrderStatus');

        // shipment methods
        $this->setActionRights(1, 'Catalog', 'ShipmentMethods');
        $this->setActionRights(1, 'Catalog', 'EditShipmentMethod');
        $this->setActionRights(1, 'Catalog', 'DisableShipmentMethod');
        $this->setActionRights(1, 'Catalog', 'EnableShipmentMethod');

        // payment methods
        $this->setActionRights(1, 'Catalog', 'PaymentMethods');
        $this->setActionRights(1, 'Catalog', 'EditPaymentMethod');
        $this->setActionRights(1, 'Catalog', 'DisablePaymentMethod');
        $this->setActionRights(1, 'Catalog', 'EnablePaymentMethod');

        // product options methods
        $this->setActionRights(1, 'Catalog', 'AddProductOption');
        $this->setActionRights(1, 'Catalog', 'EditProductOption');
        $this->setActionRights(1, 'Catalog', 'DeleteProductOption');
        $this->setActionRights(1, 'Catalog', 'SequenceProductOptions');

        // product option values methods
        $this->setActionRights(1, 'Catalog', 'AddProductOptionValue');
        $this->setActionRights(1, 'Catalog', 'EditProductOptionValue');
        $this->setActionRights(1, 'Catalog', 'DeleteProductOptionValue');
        $this->setActionRights(1, 'Catalog', 'SequenceProductOptionValues');

        // add extra's
        $this->insertExtra('Catalog', ModuleExtraType::block(), 'Catalog', 'Index');
        $this->insertExtra('Catalog', ModuleExtraType::block(), 'Catalog', 'Cart');
        $this->insertExtra('Catalog', ModuleExtraType::block(), 'Brand', 'Brand');
        $this->insertExtra('Catalog', ModuleExtraType::block(), 'Cart', 'Cart');
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
                'catalog/add_product_option',
                'catalog/edit_product_option',
                'catalog/add_product_option_value',
                'catalog/edit_product_option_value',
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
                'catalog/edit_specification',
                'catalog/edit_specification_value',
            ]
        );
        $this->setNavigation(
            $navigationCatalogId,
            'Orders',
            'catalog/orders',
            [
                'catalog/edit_order',
            ]
        );
        $this->setNavigation(
            $navigationCatalogId,
            'Brands',
            'catalog/brands',
            [
                'catalog/add_brand',
                'catalog/edit_brand',
            ]
        );
        $this->setNavigation(
            $navigationCatalogId,
            'Vats',
            'catalog/vats',
            [
                'catalog/add_vat',
                'catalog/edit_vat',
            ]
        );
        $this->setNavigation(
            $navigationCatalogId,
            'StockStatuses',
            'catalog/stock_statuses',
            [
                'catalog/add_stock_status',
                'catalog/edit_stock_status',
            ]
        );
        $this->setNavigation(
            $navigationCatalogId,
            'OrderStatuses',
            'catalog/order_statuses',
            [
                'catalog/add_order_status',
                'catalog/edit_order_status',
            ]
        );
        $this->setNavigation(
            $navigationCatalogId,
            'ShipmentMethods',
            'catalog/shipment_methods',
            [
                'catalog/edit_shipment_method',
            ]
        );
        $this->setNavigation(
            $navigationCatalogId,
            'PaymentMethods',
            'catalog/payment_methods',
            [
                'catalog/edit_payment_method',
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
        Model::get('fork.entity.create_schema')->forEntityClass(OrderStatus::class);
        Model::get('fork.entity.create_schema')->forEntityClass(Product::class);
        Model::get('fork.entity.create_schema')->forEntityClass(ProductOption::class);
        Model::get('fork.entity.create_schema')->forEntityClass(ProductOptionValue::class);
        Model::get('fork.entity.create_schema')->forEntityClass(ProductSpecial::class);
        Model::get('fork.entity.create_schema')->forEntityClass(OrderAddress::class);
        Model::get('fork.entity.create_schema')->forEntityClass(Order::class);
        Model::get('fork.entity.create_schema')->forEntityClass(OrderProduct::class);
        Model::get('fork.entity.create_schema')->forEntityClass(OrderVat::class);
        Model::get('fork.entity.create_schema')->forEntityClass(OrderHistory::class);
        Model::get('fork.entity.create_schema')->forEntityClass(Specification::class);
        Model::get('fork.entity.create_schema')->forEntityClass(SpecificationValue::class);
        Model::get('fork.entity.create_schema')->forEntityClass(Cart::class);
        Model::get('fork.entity.create_schema')->forEntityClass(CartValue::class);
        Model::get('fork.entity.create_schema')->forEntityClass(CartValueOption::class);
        Model::get('fork.entity.create_schema')->forEntityClass(ShipmentMethod::class);
        Model::get('fork.entity.create_schema')->forEntityClass(PaymentMethod::class);
    }
}

<?php

namespace Backend\Modules\Catalog\Installer;

use Backend\Core\Engine\Model;
use Backend\Core\Installer\ModuleInstaller;
use Backend\Modules\Catalog\Domain\Account\Account;
use Backend\Modules\Catalog\Domain\Brand\Brand;
use Backend\Modules\Catalog\Domain\Cart\Cart;
use Backend\Modules\Catalog\Domain\Cart\CartValue;
use Backend\Modules\Catalog\Domain\Cart\CartValueOption;
use Backend\Modules\Catalog\Domain\CartRule\CartRule;
use Backend\Modules\Catalog\Domain\Category\Category;
use Backend\Modules\Catalog\Domain\Country\Country;
use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Catalog\Domain\OrderHistory\OrderHistory;
use Backend\Modules\Catalog\Domain\OrderProduct\OrderProduct;
use Backend\Modules\Catalog\Domain\OrderProductNotification\OrderProductNotification;
use Backend\Modules\Catalog\Domain\OrderProductOption\OrderProductOption;
use Backend\Modules\Catalog\Domain\OrderRule\OrderRule;
use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatus;
use Backend\Modules\Catalog\Domain\OrderVat\OrderVat;
use Backend\Modules\Catalog\Domain\PaymentMethod\PaymentMethod;
use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\ProductDimension\ProductDimension;
use Backend\Modules\Catalog\Domain\ProductDimensionNotification\ProductDimensionNotification;
use Backend\Modules\Catalog\Domain\ProductOption\ProductOption;
use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue;
use Backend\Modules\Catalog\Domain\ProductSpecial\ProductSpecial;
use Backend\Modules\Catalog\Domain\ShipmentMethod\ShipmentMethod;
use Backend\Modules\Catalog\Domain\Specification\Specification;
use Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValue;
use Backend\Modules\Catalog\Domain\StockStatus\StockStatus;
use Backend\Modules\Catalog\Domain\UpSellProduct\UpSellProduct;
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
    public function install(): void
    {
        $this->configureEntities();
        $this->addModule('Catalog');
        $this->importLocale(__DIR__ . '/Data/locale.xml');
        $this->makeSearchable($this->getModule());
        $this->addBackendNavigation();
        $this->addModulePermissions();
        $this->addModuleSettings();
        $this->addFrontendExtras();
    }

    private function configureEntities(): void
    {
        Model::get('fork.entity.create_schema')->forEntityClasses([
            Category::class,
            Brand::class,
            Vat::class,
            StockStatus::class,
            OrderStatus::class,
            Product::class,
            ProductOption::class,
            ProductOptionValue::class,
            ProductSpecial::class,
            ProductDimension::class,
            ProductDimensionNotification::class,
            UpSellProduct::class,
            Country::class,
            OrderAddress::class,
            Order::class,
            OrderProduct::class,
            OrderProductOption::class,
            OrderVat::class,
            OrderHistory::class,
            Specification::class,
            SpecificationValue::class,
            Cart::class,
            CartValue::class,
            CartValueOption::class,
            CartRule::class,
            ShipmentMethod::class,
            PaymentMethod::class,
            Account::class,
            OrderProductNotification::class,
            OrderRule::class,
        ]);
    }

    private function addBackendNavigation(): void
    {
        $parentNavigationId = $this->setNavigation(null, $this->getModule(), 'catalog/orders', null, 4);

        // Orders
        $this->setNavigation(
            $parentNavigationId,
            'Orders',
            'catalog/orders',
            [
                'catalog/edit_order',
            ]
        );

        // Products
        $this->setNavigation(
            $parentNavigationId,
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
            $parentNavigationId,
            'Categories',
            'catalog/categories',
            [
                'catalog/add_category',
                'catalog/edit_category'
            ]
        );
        $this->setNavigation(
            $parentNavigationId,
            'Specifications',
            'catalog/specifications',
            [
                'catalog/add_specification',
                'catalog/edit_specification',
                'catalog/edit_specification_value',
            ]
        );
        $this->setNavigation(
            $parentNavigationId,
            'Brands',
            'catalog/brands',
            [
                'catalog/add_brand',
                'catalog/edit_brand',
            ]
        );
        $this->setNavigation(
            $parentNavigationId,
            'Discounts',
            'catalog/cart_rules',
            [
                'catalog/add_cart_rule',
                'catalog/edit_cart_rule',
            ]
        );
        $this->setNavigation(
            $parentNavigationId,
            'Vats',
            'catalog/vats',
            [
                'catalog/add_vat',
                'catalog/edit_vat',
            ]
        );

        // Shop settings
        $shopSettingsNavigationId = $this->setNavigation(
            $parentNavigationId,
            'ShopSettings',
            'catalog/stock_statuses'
        );
        $this->setNavigation(
            $shopSettingsNavigationId,
            'StockStatuses',
            'catalog/stock_statuses',
            [
                'catalog/add_stock_status',
                'catalog/edit_stock_status',
            ]
        );
        $this->setNavigation(
            $shopSettingsNavigationId,
            'OrderStatuses',
            'catalog/order_statuses',
            [
                'catalog/add_order_status',
                'catalog/edit_order_status',
            ]
        );
        $this->setNavigation(
            $shopSettingsNavigationId,
            'ShipmentMethods',
            'catalog/shipment_methods',
            [
                'catalog/edit_shipment_method',
            ]
        );
        $this->setNavigation(
            $shopSettingsNavigationId,
            'PaymentMethods',
            'catalog/payment_methods',
            [
                'catalog/edit_payment_method',
            ]
        );
        $this->setNavigation(
            $shopSettingsNavigationId,
            'Countries',
            'catalog/countries',
            [
                'catalog/add_country',
                'catalog/edit_country',
            ]
        );

        // settings navigation
        $navigationSettingsId = $this->setNavigation(null, 'Settings');
        $navigationModulesId  = $this->setNavigation($navigationSettingsId, 'Modules');
        $this->setNavigation($navigationModulesId, $this->getModule(), 'catalog/settings');
    }

    private function addModulePermissions(): void
    {
        // module rights
        $this->setModuleRights(1, $this->getModule());

        // products and index
        $this->setActionRights(1, $this->getModule(), 'Index');
        $this->setActionRights(1, $this->getModule(), 'Add');
        $this->setActionRights(1, $this->getModule(), 'Edit');
        $this->setActionRights(1, $this->getModule(), 'Delete');
        $this->setActionRights(1, $this->getModule(), 'Copy');
        $this->setActionRights(1, $this->getModule(), 'SequenceProducts');
        $this->setActionRights(1, $this->getModule(), 'AutoCompleteProducts');

        // categories
        $this->setActionRights(1, $this->getModule(), 'Categories');
        $this->setActionRights(1, $this->getModule(), 'AddCategory');
        $this->setActionRights(1, $this->getModule(), 'EditCategory');
        $this->setActionRights(1, $this->getModule(), 'DeleteCategory');
        $this->setActionRights(1, $this->getModule(), 'SequenceCategories');

        // specifications
        $this->setActionRights(1, $this->getModule(), 'Specifications');
        $this->setActionRights(1, $this->getModule(), 'EditSpecification');
        $this->setActionRights(1, $this->getModule(), 'AddSpecification');
        $this->setActionRights(1, $this->getModule(), 'DeleteSpecification');
        $this->setActionRights(1, $this->getModule(), 'SequenceSpecifications');

        // specification values
        $this->setActionRights(1, $this->getModule(), 'EditSpecificationValue');
        $this->setActionRights(1, $this->getModule(), 'DeleteSpecificationValue');
        $this->setActionRights(1, $this->getModule(), 'SequenceSpecificationValues');
        $this->setActionRights(1, $this->getModule(), 'AutoCompleteSpecificationValue');

        // orders
        $this->setActionRights(1, $this->getModule(), 'Orders');
        $this->setActionRights(1, $this->getModule(), 'EditOrder');
        $this->setActionRights(1, $this->getModule(), 'DeleteCompleted');
        $this->setActionRights(1, $this->getModule(), 'MassOrderAction');
        $this->setActionRights(1, $this->getModule(), 'GenerateInvoiceNumber');
        $this->setActionRights(1, $this->getModule(), 'PackingSlip');
        $this->setActionRights(1, $this->getModule(), 'Invoice');

        // settings
        $this->setActionRights(1, $this->getModule(), 'Settings');

        // brands
        $this->setActionRights(1, $this->getModule(), 'Brands');
        $this->setActionRights(1, $this->getModule(), 'AddBrand');
        $this->setActionRights(1, $this->getModule(), 'EditBrand');
        $this->setActionRights(1, $this->getModule(), 'DeleteBrand');
        $this->setActionRights(1, $this->getModule(), 'SequenceBrands');

        // vats
        $this->setActionRights(1, $this->getModule(), 'Vats');
        $this->setActionRights(1, $this->getModule(), 'AddVat');
        $this->setActionRights(1, $this->getModule(), 'EditVat');
        $this->setActionRights(1, $this->getModule(), 'DeleteVat');
        $this->setActionRights(1, $this->getModule(), 'SequenceVats');

        // stock statuses
        $this->setActionRights(1, $this->getModule(), 'StockStatuses');
        $this->setActionRights(1, $this->getModule(), 'AddStockStatus');
        $this->setActionRights(1, $this->getModule(), 'EditStockStatus');
        $this->setActionRights(1, $this->getModule(), 'DeleteStockStatus');

        // order statuses
        $this->setActionRights(1, $this->getModule(), 'OrderStatuses');
        $this->setActionRights(1, $this->getModule(), 'AddOrderStatus');
        $this->setActionRights(1, $this->getModule(), 'EditOrderStatus');
        $this->setActionRights(1, $this->getModule(), 'DeleteOrderStatus');

        // shipment methods
        $this->setActionRights(1, $this->getModule(), 'ShipmentMethods');
        $this->setActionRights(1, $this->getModule(), 'EditShipmentMethod');
        $this->setActionRights(1, $this->getModule(), 'DisableShipmentMethod');
        $this->setActionRights(1, $this->getModule(), 'EnableShipmentMethod');

        // payment methods
        $this->setActionRights(1, $this->getModule(), 'PaymentMethods');
        $this->setActionRights(1, $this->getModule(), 'EditPaymentMethod');
        $this->setActionRights(1, $this->getModule(), 'DisablePaymentMethod');
        $this->setActionRights(1, $this->getModule(), 'EnablePaymentMethod');

        // product options methods
        $this->setActionRights(1, $this->getModule(), 'AddProductOption');
        $this->setActionRights(1, $this->getModule(), 'EditProductOption');
        $this->setActionRights(1, $this->getModule(), 'DeleteProductOption');
        $this->setActionRights(1, $this->getModule(), 'SequenceProductOptions');

        // product option values methods
        $this->setActionRights(1, $this->getModule(), 'AddProductOptionValue');
        $this->setActionRights(1, $this->getModule(), 'EditProductOptionValue');
        $this->setActionRights(1, $this->getModule(), 'DeleteProductOptionValue');
        $this->setActionRights(1, $this->getModule(), 'SequenceProductOptionValues');
        $this->setActionRights(1, $this->getModule(), 'AutoCompleteProductOptionValue');

        // product option values methods
        $this->setActionRights(1, $this->getModule(), 'CartRules');
        $this->setActionRights(1, $this->getModule(), 'AddCartRule');
        $this->setActionRights(1, $this->getModule(), 'EditCartRule');
        $this->setActionRights(1, $this->getModule(), 'DeleteCartRule');

        // countries
        $this->setActionRights(1, $this->getModule(), 'Countries');
        $this->setActionRights(1, $this->getModule(), 'AddCountry');
        $this->setActionRights(1, $this->getModule(), 'EditCountry');
        $this->setActionRights(1, $this->getModule(), 'DeleteCountry');
    }

    private function addFrontendExtras(): void
    {
        $this->insertExtra($this->getModule(), ModuleExtraType::block(), 'Catalog', 'Index');
        $this->insertExtra($this->getModule(), ModuleExtraType::block(), 'Catalog', 'Cart');
        $this->insertExtra($this->getModule(), ModuleExtraType::block(), 'Brand', 'Brand');
        $this->insertExtra($this->getModule(), ModuleExtraType::block(), 'Cart', 'Cart');
        $this->insertExtra($this->getModule(), ModuleExtraType::block(), 'Search', 'Search');
        $this->insertExtra($this->getModule(), ModuleExtraType::block(), 'Register', 'Register');
        $this->insertExtra($this->getModule(), ModuleExtraType::block(), 'CustomerOrders', 'CustomerOrders');
        $this->insertExtra($this->getModule(), ModuleExtraType::block(), 'CustomerAddresses', 'CustomerAddresses');
        $this->insertExtra($this->getModule(), ModuleExtraType::block(), 'GuestOrderTracking', 'GuestOrderTracking');
        $this->insertExtra($this->getModule(), ModuleExtraType::widget(), 'Search', 'Search');
        $this->insertExtra($this->getModule(), ModuleExtraType::widget(), 'GoogleSiteSearch', 'GoogleSiteSearch');
        $this->insertExtra($this->getModule(), ModuleExtraType::widget(), 'Categories', 'Categories');
        $this->insertExtra($this->getModule(), ModuleExtraType::widget(), 'ShoppingCart', 'ShoppingCart');
        $this->insertExtra($this->getModule(), ModuleExtraType::widget(), 'RecentProducts', 'RecentProducts');
        $this->insertExtra($this->getModule(), ModuleExtraType::widget(), 'Brands', 'Brands');
    }

    private function addModuleSettings(): void
    {
        $this->setSetting($this->getModule(), 'overview_num_items', 10);
        $this->setSetting($this->getModule(), 'filters_show_more_num_items', 5);
        $this->setSetting($this->getModule(), 'next_invoice_number', (int) date('Y') . "0001");
    }
}

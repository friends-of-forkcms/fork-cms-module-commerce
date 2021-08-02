<?php

namespace Backend\Modules\Commerce\Installer;

use Backend\Core\Engine\Model;
use Backend\Core\Installer\ModuleInstaller;
use Backend\Core\Language\Language;
use Backend\Modules\Commerce\Domain\Account\Account;
use Backend\Modules\Commerce\Domain\Brand\Brand;
use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\Cart\CartValue;
use Backend\Modules\Commerce\Domain\Cart\CartValueOption;
use Backend\Modules\Commerce\Domain\CartRule\CartRule;
use Backend\Modules\Commerce\Domain\Category\Category;
use Backend\Modules\Commerce\Domain\Country\Country;
use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Commerce\Domain\OrderHistory\OrderHistory;
use Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct;
use Backend\Modules\Commerce\Domain\OrderProductNotification\OrderProductNotification;
use Backend\Modules\Commerce\Domain\OrderProductOption\OrderProductOption;
use Backend\Modules\Commerce\Domain\OrderRule\OrderRule;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus;
use Backend\Modules\Commerce\Domain\OrderVat\OrderVat;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethod;
use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\ProductDimension\ProductDimension;
use Backend\Modules\Commerce\Domain\ProductDimensionNotification\ProductDimensionNotification;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;
use Backend\Modules\Commerce\Domain\ProductSpecial\ProductSpecial;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethod;
use Backend\Modules\Commerce\Domain\Specification\Specification;
use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValue;
use Backend\Modules\Commerce\Domain\StockStatus\StockStatus;
use Backend\Modules\Commerce\Domain\UpSellProduct\UpSellProduct;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\Locale\Engine\Model as BackendLocaleModel;
use Common\ModuleExtraType;

/**
 * Installer for the Commerce module.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Jacob van Dam (Jacob van Dam ICT) <j.vandam@jvdict.nl>
 */
class Installer extends ModuleInstaller
{
    private int $commerceBlockId;
    private int $commerceCartBlockId;

    public function install(): void
    {
        $this->configureEntities();
        $this->addModule('Commerce');
        $this->importLocale(__DIR__.'/Data/locale.xml');
        $this->addBackendNavigation();
        $this->addModulePermissions();
        $this->addModuleSettings();
        $this->addFrontendExtras();
        $this->makeSearchable($this->getModule());
        $this->configureFrontendPages();
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
        $parentNavigationId = $this->setNavigation(null, $this->getModule(), 'commerce/orders', null, 4);

        // Orders
        $this->setNavigation(
            $parentNavigationId,
            'Orders',
            'commerce/orders',
            [
                'commerce/edit_order',
            ]
        );

        // Products
        $this->setNavigation(
            $parentNavigationId,
            'Products',
            'commerce/index',
            [
                'commerce/add',
                'commerce/edit',
                'commerce/add_product_option',
                'commerce/edit_product_option',
                'commerce/add_product_option_value',
                'commerce/edit_product_option_value',
            ]
        );
        $this->setNavigation(
            $parentNavigationId,
            'Categories',
            'commerce/categories',
            [
                'commerce/add_category',
                'commerce/edit_category',
            ]
        );
        $this->setNavigation(
            $parentNavigationId,
            'Specifications',
            'commerce/specifications',
            [
                'commerce/add_specification',
                'commerce/edit_specification',
                'commerce/edit_specification_value',
            ]
        );
        $this->setNavigation(
            $parentNavigationId,
            'Brands',
            'commerce/brands',
            [
                'commerce/add_brand',
                'commerce/edit_brand',
            ]
        );
        $this->setNavigation(
            $parentNavigationId,
            'Discounts',
            'commerce/cart_rules',
            [
                'commerce/add_cart_rule',
                'commerce/edit_cart_rule',
            ]
        );
        $this->setNavigation(
            $parentNavigationId,
            'Vats',
            'commerce/vats',
            [
                'commerce/add_vat',
                'commerce/edit_vat',
            ]
        );

        // Shop settings
        $shopSettingsNavigationId = $this->setNavigation(
            $parentNavigationId,
            'ShopSettings',
            'commerce/stock_statuses'
        );
        $this->setNavigation(
            $shopSettingsNavigationId,
            'StockStatuses',
            'commerce/stock_statuses',
            [
                'commerce/add_stock_status',
                'commerce/edit_stock_status',
            ]
        );
        $this->setNavigation(
            $shopSettingsNavigationId,
            'OrderStatuses',
            'commerce/order_statuses',
            [
                'commerce/add_order_status',
                'commerce/edit_order_status',
            ]
        );
        $this->setNavigation(
            $shopSettingsNavigationId,
            'ShipmentMethods',
            'commerce/shipment_methods',
            [
                'commerce/edit_shipment_method',
            ]
        );
        $this->setNavigation(
            $shopSettingsNavigationId,
            'PaymentMethods',
            'commerce/payment_methods',
            [
                'commerce/edit_payment_method',
            ]
        );
        $this->setNavigation(
            $shopSettingsNavigationId,
            'Countries',
            'commerce/countries',
            [
                'commerce/add_country',
                'commerce/edit_country',
            ]
        );

        // settings navigation
        $navigationSettingsId = $this->setNavigation(null, 'Settings');
        $navigationModulesId = $this->setNavigation($navigationSettingsId, 'Modules');
        $this->setNavigation($navigationModulesId, $this->getModule(), 'commerce/settings');
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
        $this->commerceBlockId = $this->insertExtra($this->getModule(), ModuleExtraType::block(), "Commerce");
        $this->insertExtra($this->getModule(), ModuleExtraType::block(), 'Brand', 'Brand');
        $this->commerceCartBlockId = $this->insertExtra($this->getModule(), ModuleExtraType::block(), 'Cart', 'Cart');
        $this->insertExtra($this->getModule(), ModuleExtraType::block(), 'Register', 'Register');
        $this->insertExtra($this->getModule(), ModuleExtraType::block(), 'CustomerOrders', 'CustomerOrders');
        $this->insertExtra($this->getModule(), ModuleExtraType::block(), 'CustomerAddresses', 'CustomerAddresses');
        $this->insertExtra($this->getModule(), ModuleExtraType::block(), 'GuestOrderTracking', 'GuestOrderTracking');
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
        $this->setSetting($this->getModule(), 'next_invoice_number', (int) date('Y').'0001');
    }

    private function configureFrontendPages(): void
    {
        // loop languages
        foreach ($this->getLanguages() as $language) {
            // We must regenerate the cache and define the locale we want to insert the page into
            BackendLocaleModel::buildCache($language, 'Backend');
            Language::setLocale($language);

            // check if a page with the commerce block already exists in this language
            if (!$this->hasPageWithBlockOrWidget($language, $this->commerceBlockId)) {
                $this->insertPage(
                    ['title' => Language::lbl('Shop', $this->getModule()), 'language' => $language],
                    null,
                    ['extra_id' => $this->commerceBlockId, 'position' => 'main'],
                );
            }

            // check if a page with the cart block already exists in this language
            if (!$this->hasPageWithBlockOrWidget($language, $this->commerceCartBlockId)) {
                $this->insertPage(
                    ['title' => Language::lbl('Cart', $this->getModule()), 'language' => $language, 'parent_id' => 3],
                    null,
                    ['extra_id' => $this->commerceCartBlockId, 'position' => 'main'],
                );
            }
        }
    }

    private function hasPageWithBlockOrWidget(string $language, int $extraId): bool
    {
        // @todo: Replace with a PageRepository method when it exists.
        return (bool) $this->getDatabase()->getVar(
            'SELECT 1
             FROM pages AS p
             INNER JOIN pages_blocks AS b ON b.revision_id = p.revision_id
             WHERE b.extra_id = ? AND p.language = ?
             LIMIT 1',
            [$extraId, $language]
        );
    }
}

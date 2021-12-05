<?php

namespace Backend\Modules\Commerce\Domain\PaymentMethod\Checkout;

use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Commerce\Domain\Settings\CommerceModuleSettingsRepository;
use Backend\Modules\Commerce\Domain\ShipmentMethod\Checkout\ShipmentMethodQuote;
use Backend\Modules\Commerce\Domain\ShipmentMethod\Exception\ShipmentMethodNotFound;
use Common\Core\Model;
use Common\ModulesSettings;
use Doctrine\Common\Collections\ArrayCollection;
use Frontend\Core\Language\Locale;

/**
 * The quote class helps to calculate the transaction cost for a payment method
 */
abstract class PaymentMethodQuote
{
    protected string $name;
    protected Cart $cart;
    protected OrderAddress $address;
    protected ?Locale $language;
    protected ModulesSettings $settings;
    protected CommerceModuleSettingsRepository $commerceModuleSettingsRepository;

    public function __construct(string $name, Cart $cart, OrderAddress $address, $commerceModuleSettingsRepository)
    {
        $this->name = $name;
        $this->cart = $cart;
        $this->address = $address;
        $this->language = Locale::frontendLanguage();
        $this->commerceModuleSettingsRepository = $commerceModuleSettingsRepository;
    }

    /**
     * Check that - based on the shipment method - this is a valid payment method to proceed with.
     */
    protected function isAllowedPaymentMethod(): bool
    {
        $shipmentMethod = $this->getShipmentMethod();

        // When element doesn't exist on the shipping method, it should be available for everything
        if (
            !array_key_exists('available_payment_methods', $shipmentMethod) ||
            !$shipmentMethod['available_payment_methods'] instanceof ArrayCollection
        ) {
            return true;
        }

        foreach ($shipmentMethod['available_payment_methods'] as $availablePaymentMethod) {
            if ($availablePaymentMethod->getName() === $this->name) {
                return true;
            }
        }

        return false;
    }

    private function getShipmentMethod()
    {
        [$moduleName, $optionName] = explode('.', $this->cart->getShipmentMethod());

        $quoteClassName = $this->getShipmentMethodQuoteClass($moduleName);
        if (!class_exists($quoteClassName)) {
            throw new ShipmentMethodNotFound('Class ' . $quoteClassName . ' not found');
        }

        /** @var ShipmentMethodQuote $class */
        $class = new $quoteClassName(
            $optionName,
            $this->cart,
            $this->cart->getShipmentAddress(),
            $this->commerceModuleSettingsRepository
        );

        return $class->getQuote()[$optionName];
    }

    /**
     * We expect a Quote class to be implemented by the shipping method module
     */
    private function getShipmentMethodQuoteClass(string $moduleName): string
    {
        $domainName = str_replace('Commerce', '', $moduleName);

        return "\\Backend\\Modules\\$moduleName\\Domain\\$domainName\\Checkout\\Quote";
    }

    /**
     * Get the quote based on our payment data. A quote can return multiple options.
     */
    abstract public function getQuote(): array;
}

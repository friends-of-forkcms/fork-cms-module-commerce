<?php

namespace Backend\Modules\Commerce\PaymentMethods\Base\Checkout;

use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethod;
use Common\Core\Model;
use Common\ModulesSettings;
use Doctrine\Common\Collections\ArrayCollection;
use Frontend\Core\Language\Locale;

abstract class Quote
{
    protected string $name;
    protected Cart $cart;
    protected OrderAddress $address;
    protected ModulesSettings $settings;
    protected ?Locale $language;

    public function __construct(string $name, Cart $cart, OrderAddress $address)
    {
        $this->name = $name;
        $this->cart = $cart;
        $this->address = $address;
        $this->settings = Model::get('fork.settings');
        $this->language = Locale::frontendLanguage();
    }

    /**
     * Get a setting.
     *
     * @param mixed $defaultValue
     * @param bool  $includeLanguage
     *
     * @return mixed
     */
    protected function getSetting(string $key, $defaultValue = null, $includeLanguage = true)
    {
        $baseKey = $this->name;

        if ($includeLanguage) {
            $baseKey .= '_'.$this->language->getLocale();
        }

        return $this->settings->get('Commerce', $baseKey.'_'.$key, $defaultValue);
    }

    protected function isAllowedPaymentMethod(): bool
    {
        $shipmentMethod = $this->getShipmentMethod();

        // When element doesn't exists it should be available for everything
        if (
            !array_key_exists('available_payment_methods', $shipmentMethod) ||
            !$shipmentMethod['available_payment_methods'] instanceof ArrayCollection
        ) {
            return true;
        }

        /**
         * @var PaymentMethod $availablePaymentMethod
         */
        foreach ($shipmentMethod['available_payment_methods'] as $availablePaymentMethod) {
            if ($availablePaymentMethod->getName() === $this->name) {
                return true;
            }
        }

        return false;
    }

    private function getShipmentMethod()
    {
        $data = explode('.', $this->cart->getShipmentMethod());

        $className = "\\Backend\\Modules\\Commerce\\ShipmentMethods\\{$data[0]}\\Checkout\\Quote";
        $class = new $className($data[0], $this->cart, $this->cart->getShipmentAddress());

        return $class->getQuote()[$data[1]];
    }

    /**
     * Get the quote based on our payment data. A quote can return multiple options.
     */
    abstract public function getQuote(): array;
}

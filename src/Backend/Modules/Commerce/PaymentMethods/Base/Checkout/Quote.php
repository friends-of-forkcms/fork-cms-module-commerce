<?php

namespace Backend\Modules\Commerce\PaymentMethods\Base\Checkout;

use Backend\Modules\Commerce\Domain\Account\AddressDataTransferObject;
use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethod;
use Common\Core\Model;
use Common\ModulesSettings;
use Doctrine\Common\Collections\ArrayCollection;
use Frontend\Core\Language\Language;
use Frontend\Core\Language\Locale;

abstract class Quote
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var OrderAddress
     */
    protected $address;

    /**
     * @var ModulesSettings
     */
    protected $settings;

    /**
     * @var Language
     */
    protected $language;

    /**
     * Quote constructor.
     *
     * @param string $name
     * @param Cart $cart
     * @param AddressDataTransferObject $address
     */
    public function __construct(string $name, Cart $cart, OrderAddress $address)
    {
        $this->name = $name;
        $this->cart = $cart;
        $this->address = $address;
        $this->settings = Model::get('fork.settings');
        $this->language = Locale::frontendLanguage();
    }

    /**
     * Get a setting
     *
     * @param string $key
     * @param mixed $defaultValue
     * @param boolean $includeLanguage
     *
     * @return mixed
     */
    protected function getSetting(string $key, $defaultValue = null, $includeLanguage = true)
    {
        $baseKey = $this->name;

        if ($includeLanguage) {
            $baseKey .= '_'. $this->language->getLocale();
        }

        return $this->settings->get('Commerce', $baseKey .'_'. $key, $defaultValue);
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
            if ($availablePaymentMethod->getName() == $this->name) {
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
     *
     * @return array
     */
    abstract public function getQuote(): array;
}

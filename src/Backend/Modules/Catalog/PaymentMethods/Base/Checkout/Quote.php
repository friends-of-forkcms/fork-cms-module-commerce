<?php

namespace Backend\Modules\Catalog\PaymentMethods\Base\Checkout;

use Backend\Modules\Catalog\Domain\Account\AddressDataTransferObject;
use Backend\Modules\Catalog\Domain\Cart\Cart;
use Common\Core\Model;
use Common\ModulesSettings;
use Frontend\Core\Language\Language;
use Frontend\Core\Language\Locale;

abstract class Quote {
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var AddressDataTransferObject
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
    public function __construct(string $name, Cart $cart, AddressDataTransferObject $address)
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

        return $this->settings->get('Catalog', $baseKey .'_'. $key, $defaultValue);
    }

    /**
     * Get the quote based on our payment data. A quote can return multiple options.
     *
     * @return array
     */
    public abstract function getQuote(): array;
}

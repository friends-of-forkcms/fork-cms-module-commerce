<?php

namespace Backend\Modules\Commerce\ShipmentMethods\Base\Checkout;

use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Commerce\Domain\Vat\VatRepository;
use Common\Core\Model;
use Common\ModulesSettings;
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
            $baseKey .= '_' . $this->language->getLocale();
        }

        return $this->settings->get('Commerce', $baseKey . '_' . $key, $defaultValue);
    }

    /**
     * Get the quote based on our shipment data. A quote can return multiple options.
     */
    abstract public function getQuote(): array;

    /**
     * Calculate the vat price based on the given price.
     */
    abstract protected function getVatPrice(float $price): array;

    /**
     * Get the vat repository.
     */
    protected function getVatRepository(): VatRepository
    {
        return Model::get('commerce.repository.vat');
    }
}

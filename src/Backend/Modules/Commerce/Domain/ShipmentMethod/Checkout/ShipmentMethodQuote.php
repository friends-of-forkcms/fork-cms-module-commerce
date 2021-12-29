<?php

namespace Backend\Modules\Commerce\Domain\ShipmentMethod\Checkout;

use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Commerce\Domain\Settings\CommerceModuleSettingsRepository;
use Frontend\Core\Language\Locale;
use Money\Money;
use Tbbc\MoneyBundle\Formatter\MoneyFormatter;

/**
 * The quote class helps to calculate the shipping costs for a shipping method
 */
abstract class ShipmentMethodQuote
{
    protected string $name;
    protected Cart $cart;
    protected OrderAddress $address;
    protected ?Locale $language;
    protected CommerceModuleSettingsRepository $shipmentMethodSettingsRepository;
    protected MoneyFormatter $moneyFormatter;

    public function __construct(
        string $name,
        Cart $cart,
        OrderAddress $address,
        CommerceModuleSettingsRepository $commerceModuleSettingsRepository,
        MoneyFormatter $moneyFormatter
    ) {
        $this->name = $name;
        $this->cart = $cart;
        $this->address = $address;
        $this->language = Locale::frontendLanguage();
        $this->shipmentMethodSettingsRepository = $commerceModuleSettingsRepository;
        $this->moneyFormatter = $moneyFormatter;
    }

    /**
     * Get the quote based on our shipment data. A quote can return multiple options.
     */
    abstract public function getQuote(): array;

    /**
     * Calculate the vat price based on the given price.
     */
    abstract protected function getVatPrice(Money $price): array;
}

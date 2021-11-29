<?php

namespace Backend\Modules\CommercePickup\Domain\Pickup\Checkout;

use Backend\Modules\Commerce\Domain\ShipmentMethod\Checkout\ShipmentMethodQuote;
use Backend\Modules\Commerce\Domain\Vat\Exception\VatNotFound;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use NumberFormatter;

class Quote extends ShipmentMethodQuote
{
    /**
     * {@inheritdoc}
     */
    public function getQuote(): array
    {
        /** @var Money $price */
        $price = $this->shipmentMethodSettingsRepository->getSetting('CommercePickup', 'price');
        $numberFormatter = new NumberFormatter($this->language->getLocale(), NumberFormatter::CURRENCY);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());

        $availablePaymentMethods = $this->shipmentMethodSettingsRepository
            ->getSetting('CommercePickup', 'availablePaymentMethods');

        return [
            $this->name => [
                'label' => "{$this->name} ({$moneyFormatter->format($price)}",
                'name' => $this->name,
                'price' => $price,
                'vat' => $this->getVatPrice($price),
                'available_payment_methods' => $availablePaymentMethods,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getVatPrice(Money $price): array
    {
        $vat = $this->shipmentMethodSettingsRepository->getSetting('CommercePickup', 'vat');
        if (!$vat instanceof Vat) {
            throw VatNotFound::forEmptyId();
        }

        return [
            'id' => $vat->getId(),
            'price' => $price->multiply($vat->getAsPercentage()),
        ];
    }
}

<?php

namespace Backend\Modules\CommercePickup\Domain\Pickup\Checkout;

use Backend\Modules\Commerce\Domain\ShipmentMethod\Checkout\ShipmentMethodQuote;
use Backend\Modules\Commerce\Domain\Vat\Exception\VatNotFound;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Money\Money;

class Quote extends ShipmentMethodQuote
{
    /**
     * {@inheritdoc}
     */
    public function getQuote(): array
    {
        /** @var Money $price */
        $price = $this->shipmentMethodSettingsRepository->getSetting('CommercePickup', 'price');
        $availablePaymentMethods = $this->shipmentMethodSettingsRepository
            ->getSetting('CommercePickup', 'availablePaymentMethods');

        return [
            $this->name => [
                'label' => "{$this->name} ({$this->moneyFormatter->localizedFormatMoney($price)})",
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
        $vatId = $this->shipmentMethodSettingsRepository->getSetting('CommercePickup', 'vatId');
        $vat = $this->vatRepository->find($vatId);
        if (!$vat instanceof Vat) {
            throw VatNotFound::forEmptyId();
        }

        return [
            'id' => $vat->getId(),
            'price' => $price->multiply($vat->getAsPercentage()),
        ];
    }
}

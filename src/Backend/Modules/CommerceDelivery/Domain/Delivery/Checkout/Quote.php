<?php

namespace Backend\Modules\CommerceDelivery\Domain\Delivery\Checkout;

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
        // General setting
        $availablePaymentMethods = $this->shipmentMethodSettingsRepository->getSetting('CommerceDelivery', 'availablePaymentMethods');

        // Country specific shipment methods
        $shipmentMethods = $this->shipmentMethodSettingsRepository->getSetting('CommerceDelivery', 'shipmentMethods');
        $enabledShipmentMethods = [];

        foreach ($shipmentMethods as $shipmentMethod) {
            if (!$shipmentMethod['enabled']) {
                continue;
            }

            $enabledShipmentMethods[$shipmentMethod['label']] = [
                'label' => "{$shipmentMethod['label']} ({$this->moneyFormatter->localizedFormatMoney($shipmentMethod['price'])})",
                'name' => $shipmentMethod['label'],
                'price' => $shipmentMethod['price'],
                'vat' => $this->getVatPrice($shipmentMethod['price']),
                'available_payment_methods' => $availablePaymentMethods,
            ];
        }

        if ($enabledShipmentMethods) {
            return $enabledShipmentMethods;
        }

        // No enabled country-specific methods -> fallback to default delivery pricing
        /** @var Money $price */
        $price = $this->shipmentMethodSettingsRepository->getSetting('CommerceDelivery', 'price');

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
        $vatId = $this->shipmentMethodSettingsRepository->getSetting('CommerceDelivery', 'vatId');
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

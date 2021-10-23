<?php

namespace Backend\Modules\Commerce\ShipmentMethods\Delivery\Checkout;

use Backend\Modules\Commerce\Domain\Vat\Exception\VatNotFound;
use Backend\Modules\Commerce\ShipmentMethods\Base\Checkout\Quote as BaseQuote;

class Quote extends BaseQuote
{
    /**
     * {@inheritdoc}
     */
    public function getQuote(): array
    {
        return [
            $this->name => [
                'label' => $this->getSetting('name') . ' (&euro; ' . number_format($this->getSetting('price'), 2, ',', '.') . ')',
                'name' => $this->getSetting('name'),
                'price' => (float) $this->getSetting('price'),
                'vat' => $this->getVatPrice((float) $this->getSetting('price')),
                'available_payment_methods' => $this->getSetting('available_payment_methods'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getVatPrice(float $price): array
    {
        try {
            $vat = $this->getVatRepository()->findOneByIdAndLocale($this->getSetting('vat'), $this->language);
        } catch (VatNotFound $e) {
            $vat = 0;
        }

        return [
            'id' => $vat->getId(),
            'price' => $price * $vat->getAsPercentage(),
        ];
    }
}

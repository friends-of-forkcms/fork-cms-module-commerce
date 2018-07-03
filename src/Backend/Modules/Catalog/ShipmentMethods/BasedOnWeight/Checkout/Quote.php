<?php

namespace Backend\Modules\Catalog\ShipmentMethods\BasedOnWeight\Checkout;

use Backend\Modules\Catalog\Domain\Vat\Exception\VatNotFound;
use Backend\Modules\Catalog\ShipmentMethods\Base\Checkout\Quote as BaseQuote;
use Backend\Modules\Catalog\ShipmentMethods\BasedOnWeight\ValueDataTransferObject;

class Quote extends BaseQuote
{
    /**
     * {@inheritdoc}
     */
    public function getQuote(): array
    {
        $quote = [];

        /**
         * @var ValueDataTransferObject $value
         */
        foreach ($this->getSetting('values') as $key => $value) {
            if ($value->fromWeight !== null && $this->cart->getTotalWeight() < $value->fromWeight) {
                continue;
            }

            if ($value->tillWeight !== null && $this->cart->getTotalWeight() > $value->tillWeight) {
                continue;
            }

            $price = (float)$value->price;
            $vatPrice = $this->getVatPrice($price);
            $totalPrice = $price + $vatPrice['price'];

            $quote[$this->name . '_' . $key] = [
                'label' => $value->name . ' (&euro; ' . number_format($totalPrice, 2, ',', '.') . ')',
                'name' => $value->name,
                'price' => $price,
                'vat' => $vatPrice,
            ];
        }

        return $quote;
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
            'price' => (float) $price * $vat->getAsPercentage(),
        ];
    }
}

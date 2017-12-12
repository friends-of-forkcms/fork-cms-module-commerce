<?php

namespace Backend\Modules\Catalog\ShipmentMethods\Pickup\Checkout;

use Backend\Modules\Catalog\ShipmentMethods\Base\Checkout\Quote as BaseQuote;

class Quote extends BaseQuote
{
    /**
     * {@inheritdoc}
     */
    public function getQuote(): array
    {
        return [
            $this->name => [
                'label' => $this->getSetting('name') .' (&euro; '. number_format($this->getSetting('price'), 2, ',', '.') .')',
                'name' => $this->getSetting('name'),
                'price' => (float) $this->getSetting('price'),
                'vat' => $this->getVatPrice(),
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getVatPrice(): array
    {
        $vat = $this->getVatRepository()->findOneByIdAndLocale($this->getSetting('vat'), $this->language);
        return [
            'id' => $vat->getId(),
            'price' => (float) $this->getSetting('price') * $vat->getAsPercentage(),
        ];
    }
}

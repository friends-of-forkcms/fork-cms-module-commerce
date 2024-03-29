<?php

namespace Backend\Modules\CommerceCashOnDelivery\Domain\CashOnDelivery\Checkout;

use Backend\Modules\Commerce\Domain\PaymentMethod\Checkout\PaymentMethodQuote;
use Money\Money;

class Quote extends PaymentMethodQuote
{
    /**
     * {@inheritdoc}
     */
    public function getQuote(): array
    {
        if (!$this->isAllowedPaymentMethod()) {
            return [];
        }

        /** @var Money $price */
        $label = $this->name;
        $price = $this->commerceModuleSettingsRepository->getSetting('CommerceCashOnDelivery', 'price');
        if ($price !== null) {
            $label .= ' (' . $this->moneyFormatter->localizedFormatMoney($price) . ')';
        }

        return [
            $this->name => [
                'label' => $label,
                'name' => $this->name,
                'price' => $price ?? Money::EUR(0),
            ],
        ];
    }
}

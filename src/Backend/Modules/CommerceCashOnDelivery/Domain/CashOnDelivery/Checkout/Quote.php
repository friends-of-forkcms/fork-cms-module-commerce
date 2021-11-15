<?php

namespace Backend\Modules\CommerceCashOnDelivery\Domain\CashOnDelivery\Checkout;

use Backend\Modules\Commerce\Domain\PaymentMethod\Checkout\PaymentMethodQuote;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use NumberFormatter;

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
            $numberFormatter = new NumberFormatter($this->language->getLocale(), NumberFormatter::CURRENCY);
            $moneyFormatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());
            $label .= ' (' . $moneyFormatter->format($price) . ')';
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

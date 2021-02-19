<?php

namespace Backend\Modules\Catalog\PaymentMethods\Buckaroo\Checkout;

use Backend\Modules\Catalog\PaymentMethods\Base\Checkout\Quote as BaseQuote;

class Quote extends BaseQuote
{
    /**
     * {@inheritdoc}
     */
    public function getQuote(): array
    {
        if (!$this->isAllowedPaymentMethod()) {
            return [];
        }

        $paymentMethods = $this->getSetting('paymentMethods');
        $enabledPaymentMethods = [];

        foreach ($paymentMethods as $key => $paymentMethod) {
            if (!$paymentMethod['enabled']) {
                continue;
            }

            $enabledPaymentMethods[$key] = [
                'label' => $paymentMethod['label'],
                'name' => $key,
            ];
        }

        return $enabledPaymentMethods;
    }
}

<?php

namespace Backend\Modules\CommerceMollie\Domain\Mollie\Checkout;

use Backend\Modules\Commerce\Domain\PaymentMethod\Checkout\PaymentMethodQuote;

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

        $paymentMethods = $this->commerceModuleSettingsRepository->getSetting('CommerceMollie', 'paymentMethods');
        if (!$paymentMethods || empty($paymentMethods)) {
            return [];
        }

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

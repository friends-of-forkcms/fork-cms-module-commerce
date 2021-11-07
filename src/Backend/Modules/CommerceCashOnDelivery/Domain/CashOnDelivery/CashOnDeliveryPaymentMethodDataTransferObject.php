<?php

namespace Backend\Modules\CommerceCashOnDelivery\Domain\CashOnDelivery;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethod;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethodDataTransferObject;
use Symfony\Component\Validator\Constraints as Assert;

class CashOnDeliveryPaymentMethodDataTransferObject extends PaymentMethodDataTransferObject
{
    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $orderInitId = null;

    public function __construct(PaymentMethod $paymentMethod = null, Locale $locale)
    {
        $this->name = 'Cash on delivery'; // default name filled in the UI
        $this->module = 'CommerceCashOnDelivery';
        parent::__construct($paymentMethod, $locale);
    }
}

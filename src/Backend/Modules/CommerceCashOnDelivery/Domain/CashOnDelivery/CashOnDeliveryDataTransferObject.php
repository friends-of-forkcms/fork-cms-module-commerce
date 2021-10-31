<?php

namespace Backend\Modules\CommerceCashOnDelivery\Domain\CashOnDelivery;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethod;
use Backend\Modules\Commerce\PaymentMethods\Base\DataTransferObject;
use Symfony\Component\Validator\Constraints as Assert;

class CashOnDeliveryDataTransferObject extends DataTransferObject
{
    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $orderInitId;

    public function __construct(PaymentMethod $paymentMethod = null, Locale $locale)
    {
        $this->name = 'Cash on delivery'; // default name filled in the UI
        $this->module = 'CommerceCashOnDelivery';
        parent::__construct($paymentMethod, $locale);
    }
}

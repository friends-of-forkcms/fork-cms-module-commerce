<?php

namespace Backend\Modules\Commerce\PaymentMethods\CashOnDelivery;

use Backend\Modules\Commerce\PaymentMethods\Base\DataTransferObject;
use Symfony\Component\Validator\Constraints as Assert;

class CashOnDeliveryDataTransferObject extends DataTransferObject
{
    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $name;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $orderInitId;
}

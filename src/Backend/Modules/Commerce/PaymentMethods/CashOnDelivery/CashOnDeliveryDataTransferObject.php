<?php

namespace Backend\Modules\Commerce\PaymentMethods\CashOnDelivery;

use Backend\Modules\Commerce\PaymentMethods\Base\DataTransferObject;
use Symfony\Component\Validator\Constraints as Assert;

class CashOnDeliveryDataTransferObject extends DataTransferObject
{
    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $name;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $orderInitId;
}

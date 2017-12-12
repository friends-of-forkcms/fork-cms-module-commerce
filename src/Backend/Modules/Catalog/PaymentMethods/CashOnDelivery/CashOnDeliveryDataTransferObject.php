<?php

namespace Backend\Modules\Catalog\PaymentMethods\CashOnDelivery;

use Backend\Modules\Catalog\PaymentMethods\Base\DataTransferObject;
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

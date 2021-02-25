<?php

namespace Backend\Modules\Commerce\PaymentMethods\Base;

use Symfony\Component\Validator\Constraints as Assert;

abstract class DataTransferObject
{
    /**
     * @var bool
     */
    public $installed = false;
}

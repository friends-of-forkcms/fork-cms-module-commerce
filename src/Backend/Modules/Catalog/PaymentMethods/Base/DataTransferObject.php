<?php

namespace Backend\Modules\Catalog\PaymentMethods\Base;

use Symfony\Component\Validator\Constraints as Assert;

abstract class DataTransferObject
{
    /**
     * @var bool
     */
    public $installed = false;
}

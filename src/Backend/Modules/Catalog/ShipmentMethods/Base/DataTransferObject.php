<?php

namespace Backend\Modules\Catalog\ShipmentMethods\Base;

use Symfony\Component\Validator\Constraints as Assert;

abstract class DataTransferObject
{
    /**
     * @var bool
     */
    public $installed = false;
}

<?php

namespace Backend\Modules\Commerce\ShipmentMethods\Base;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

abstract class DataTransferObject
{
    /**
     * @var bool
     */
    public $installed = false;

    /**
     * @var ArrayCollection
     */
    public $available_payment_methods;
}

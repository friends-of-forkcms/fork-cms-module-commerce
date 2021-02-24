<?php

namespace Backend\Modules\Commerce\ShipmentMethods\Base;

use Doctrine\Common\Collections\Collection;

abstract class DataTransferObject
{
    public bool $installed = false;
    public Collection $available_payment_methods;
}

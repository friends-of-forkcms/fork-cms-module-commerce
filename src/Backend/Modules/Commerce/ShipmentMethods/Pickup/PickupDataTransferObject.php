<?php

namespace Backend\Modules\Commerce\ShipmentMethods\Pickup;

use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\Commerce\ShipmentMethods\Base\DataTransferObject;
use Symfony\Component\Validator\Constraints as Assert;

class PickupDataTransferObject extends DataTransferObject
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
    public $price;

    /**
     * @var Vat
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $vat;

    public function __construct()
    {
    }
}

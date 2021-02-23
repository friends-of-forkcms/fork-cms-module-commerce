<?php

namespace Backend\Modules\Commerce\ShipmentMethods\Delivery;

use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\Commerce\ShipmentMethods\Base\DataTransferObject;
use Symfony\Component\Validator\Constraints as Assert;

class DeliveryDataTransferObject extends DataTransferObject
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

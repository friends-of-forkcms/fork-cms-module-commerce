<?php

namespace Backend\Modules\Catalog\ShipmentMethods\Delivery;

use Backend\Modules\Catalog\Domain\Vat\Vat;
use Backend\Modules\Catalog\ShipmentMethods\Base\DataTransferObject;
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

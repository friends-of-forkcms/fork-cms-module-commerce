<?php

namespace Backend\Modules\Catalog\Domain\ShipmentMethod;

use Symfony\Component\Validator\Constraints as Assert;

class CheckoutShipmentMethodDataTransferObject
{
    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $shipment_method;
}

<?php

namespace Backend\Modules\Commerce\Domain\ShipmentMethod;

use Symfony\Component\Validator\Constraints as Assert;

class CheckoutShipmentMethodDataTransferObject
{
    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $shipment_method;
}

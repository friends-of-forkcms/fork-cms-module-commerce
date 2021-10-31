<?php

namespace Backend\Modules\Commerce\ShipmentMethods\Delivery;

use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\Commerce\ShipmentMethods\Base\DataTransferObject;
use Symfony\Component\Validator\Constraints as Assert;

class DeliveryDataTransferObject extends DataTransferObject
{
    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $name = 'Standard delivery';

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $price;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public Vat $vat;
}

<?php

namespace Backend\Modules\Commerce\ShipmentMethods\Pickup;

use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\Commerce\ShipmentMethods\Base\DataTransferObject;
use Money\Money;
use Symfony\Component\Validator\Constraints as Assert;

class PickupDataTransferObject extends DataTransferObject
{
    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $name = 'Pickup shipment';

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?Money $price;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?Vat $vat;

    public function __construct()
    {
        parent::__construct();
        $this->price = null;
        $this->vat = null;
    }
}

<?php

namespace Backend\Modules\CommercePickup\Domain\Pickup;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethod;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethodDataTransferObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Money\Money;
use Symfony\Component\Validator\Constraints as Assert;

class PickupShipmentMethodDataTransferObject extends ShipmentMethodDataTransferObject
{
    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?Money $price;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?int $vatId;

    public ?Collection $availablePaymentMethods;

    public function __construct(ShipmentMethod $shipmentMethod = null, Locale $locale)
    {
        $this->name = 'Pickup shipment'; // default name filled in the UI
        $this->module = 'CommercePickup';
        parent::__construct($shipmentMethod, $locale);
        $this->price = null;
        $this->vatId = null;
        $this->availablePaymentMethods = new ArrayCollection();
    }
}

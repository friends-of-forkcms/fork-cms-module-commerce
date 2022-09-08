<?php

namespace Backend\Modules\CommerceDelivery\Domain\Delivery;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethod;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethodDataTransferObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Money\Money;
use Symfony\Component\Validator\Constraints as Assert;

class DeliveryShipmentMethodDataTransferObject extends ShipmentMethodDataTransferObject
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

    public $shipmentMethods = [];

    public function __construct(ShipmentMethod $shipmentMethod = null, Locale $locale)
    {
        $this->name = 'Delivery shipment'; // default name filled in the UI
        $this->module = 'CommerceDelivery';
        parent::__construct($shipmentMethod, $locale);
        $this->price = null;
        $this->vatId = null;
        $this->availablePaymentMethods = new ArrayCollection();
    }

    public function __set($key, $value)
    {
        $this->shipmentMethods[$key] = $value;
    }

    public function __get($key)
    {
        if (!isset($this->shipmentMethods[$key])) {
            return null;
        }

        return $this->shipmentMethods[$key];
    }
}

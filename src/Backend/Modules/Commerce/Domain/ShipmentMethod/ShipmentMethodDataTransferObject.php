<?php

namespace Backend\Modules\Commerce\Domain\ShipmentMethod;

use Backend\Core\Language\Locale;
use Symfony\Component\Validator\Constraints as Assert;

abstract class ShipmentMethodDataTransferObject
{
    protected ?ShipmentMethod $shipmentMethod = null;
    public int $id;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $name;
    public string $module;
    public bool $isEnabled = false;
    public Locale $locale;

    public function __construct(ShipmentMethod $shipmentMethod = null, Locale $locale)
    {
        $this->locale = $locale;
        $this->shipmentMethod = $shipmentMethod;

        if (!$this->hasExistingShipmentMethod()) {
            return;
        }

        $this->id = $this->shipmentMethod->getId();
        $this->name = $this->shipmentMethod->getName();
        $this->module = $this->shipmentMethod->getModule();
        $this->isEnabled = $this->shipmentMethod->isEnabled();
    }

    public function setShipmentMethod(ShipmentMethod $shipmentMethod): void
    {
        $this->shipmentMethod = $shipmentMethod;
    }

    public function getShipmentMethod(): ShipmentMethod
    {
        return $this->shipmentMethod;
    }

    public function hasExistingShipmentMethod(): bool
    {
        return $this->shipmentMethod instanceof ShipmentMethod;
    }
}

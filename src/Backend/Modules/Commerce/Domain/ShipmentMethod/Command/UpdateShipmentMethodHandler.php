<?php

namespace Backend\Modules\Commerce\Domain\ShipmentMethod\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethod;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethodDataTransferObject;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethodRepository;
use Common\ModulesSettings;

class UpdateShipmentMethodHandler
{
    private ShipmentMethodRepository $shipmentMethodRepository;
    private ModulesSettings $settings;
    private Locale $locale;

    public function __construct(ShipmentMethodRepository $shipmentMethodRepository, ModulesSettings $settings)
    {
        $this->shipmentMethodRepository = $shipmentMethodRepository;
        $this->settings = $settings;
        $this->locale = Locale::workingLocale();
    }

    public function handle(ShipmentMethodDataTransferObject $dataTransferObject): void
    {
        // Save the shipment method to Fork Settings
        $this->setData($dataTransferObject, true);

        // Install/update the shipment method
        $shipmentMethod = ShipmentMethod::fromDataTransferObject($dataTransferObject);
        $this->shipmentMethodRepository->add($shipmentMethod);
        $dataTransferObject->setShipmentMethod($shipmentMethod);
    }

    /**
     * Store data transfer object with the form data.
     */
    private function setData(ShipmentMethodDataTransferObject $dataTransferObject, bool $includeLanguage): void
    {
        // Get the public vars
        $properties = get_class_vars(get_class($dataTransferObject));
        $skipProperties = get_class_vars(ShipmentMethodDataTransferObject::class);

        // Assign the properties to object transfer object
        foreach ($properties as $property => $value) {
            // Skip the values that are saved already on the ShipmentMethod entity
            if (array_key_exists($property, $skipProperties)) {
                continue;
            }

            $key = $this->getBaseKey($dataTransferObject, $includeLanguage) . '_' . $property;
            $value = $dataTransferObject->{$property};

            $this->settings->set('Commerce', $key, $value);
        }
    }

    /**
     * Get the settings base key.
     */
    private function getBaseKey(ShipmentMethodDataTransferObject $dataTransferObject, bool $includeLanguage): string
    {
        $key = $dataTransferObject->module;

        if ($includeLanguage) {
            $key .= '_' . $this->locale;
        }

        return $key;
    }
}

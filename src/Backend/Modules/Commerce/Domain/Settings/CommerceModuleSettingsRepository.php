<?php

namespace Backend\Modules\Commerce\Domain\Settings;

use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethodDataTransferObject;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethodDataTransferObject;
use Common\Locale;
use Common\ModulesSettings;

/**
 * Shipment and payment modules store their specific settings in the modules_settings table, but prefixed
 * with a certain key. This class helps to fetch that data.
 */
class CommerceModuleSettingsRepository
{
    private ModulesSettings $settings;
    private Locale $locale;

    public function __construct(ModulesSettings $settings, Locale $locale)
    {
        $this->settings = $settings;
        $this->locale = $locale;
    }

    public function getSetting(
        string $moduleName,
        string $property,
        bool $includeLanguage = true,
        $defaultValue = null
    ) {
        $key = $this->getBaseKey($moduleName, $includeLanguage) . '_' . $property;

        return $this->settings->get('Commerce', $key, $defaultValue);
    }

    public function getShipmentMethodData(
        ShipmentMethodDataTransferObject $dataTransferObject,
        bool $includeLanguage = true
    ): ShipmentMethodDataTransferObject {
        return $this->getCommerceModuleData(
            ShipmentMethodDataTransferObject::class,
            $dataTransferObject,
            $includeLanguage
        );
    }

    public function getPaymentMethodData(
        PaymentMethodDataTransferObject $dataTransferObject,
        bool $includeLanguage = true
    ): PaymentMethodDataTransferObject {
        return $this->getCommerceModuleData(
            PaymentMethodDataTransferObject::class,
            $dataTransferObject,
            $includeLanguage
        );
    }

    /**
     * Populate data transfer object with data from the database.
     */
    private function getCommerceModuleData(
        $baseDataTransfersObject,
        $dataTransferObject,
        bool $includeLanguage = true
    ) {
        // Get the public vars
        $properties = get_class_vars(get_class($dataTransferObject));
        $skipProperties = get_class_vars($baseDataTransfersObject);

        // Assign the properties to object transfer object
        foreach ($properties as $property => $defaultValue) {
            // Skip the values that are saved already on the ShipmentMethod entity
            if (array_key_exists($property, $skipProperties)) {
                continue;
            }

            $key = $this->getBaseKey($dataTransferObject->module, $includeLanguage) . '_' . $property;
            $value = $this->settings->get('Commerce', $key, $defaultValue);
            $dataTransferObject->{$property} = $value;
        }

        return $dataTransferObject;
    }

    public function setShipmentMethodData(
        ShipmentMethodDataTransferObject $dataTransferObject,
        bool $includeLanguage = true
    ): void {
        $this->setCommerceModuleData(
            ShipmentMethodDataTransferObject::class,
            $dataTransferObject,
            $includeLanguage
        );
    }

    public function setPaymentMethodData(
        PaymentMethodDataTransferObject $dataTransferObject,
        bool $includeLanguage = true
    ): void {
        $this->setCommerceModuleData(
            PaymentMethodDataTransferObject::class,
            $dataTransferObject,
            $includeLanguage
        );
    }

    /**
     * Store data transfer object with the form data.
     */
    private function setCommerceModuleData(
        $baseDataTransferObject,
        $dataTransferObject,
        bool $includeLanguage
    ): void {
        // Get the public vars
        $properties = get_class_vars(get_class($dataTransferObject));
        $skipProperties = get_class_vars($baseDataTransferObject);

        // Assign the properties to object transfer object
        foreach ($properties as $property => $value) {
            // Skip the values that are saved already on the ShipmentMethod entity
            if (array_key_exists($property, $skipProperties)) {
                continue;
            }

            $key = $this->getBaseKey($dataTransferObject->module, $includeLanguage) . '_' . $property;
            $value = $dataTransferObject->{$property};

            $this->settings->set('Commerce', $key, $value);
        }
    }

    /**
     * Get the settings base key for the shipment method module.
     */
    private function getBaseKey(string $moduleName, bool $includeLanguage): string
    {
        $key = $moduleName;

        if ($includeLanguage) {
            $key .= '_' . $this->locale;
        }

        return $key;
    }
}

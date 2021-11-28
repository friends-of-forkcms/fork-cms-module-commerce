<?php

namespace Backend\Modules\Commerce\Domain\ShipmentMethod\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Settings\CommerceModuleSettingsRepository;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethod;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethodDataTransferObject;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethodRepository;
use Common\ModulesSettings;

class UpdateShipmentMethodHandler
{
    private ShipmentMethodRepository $shipmentMethodRepository;
    private CommerceModuleSettingsRepository $shipmentMethodSettingsRepository;

    public function __construct(ShipmentMethodRepository $shipmentMethodRepository, ModulesSettings $settings)
    {
        $this->shipmentMethodRepository = $shipmentMethodRepository;
        $this->shipmentMethodSettingsRepository = new CommerceModuleSettingsRepository(
            $settings,
            Locale::workingLocale()
        );
    }

    public function handle(ShipmentMethodDataTransferObject $dataTransferObject): void
    {
        // Save the shipment method to Fork Settings
        $this->shipmentMethodSettingsRepository->setShipmentMethodData($dataTransferObject, true);

        // Install/update the shipment method
        $shipmentMethod = ShipmentMethod::fromDataTransferObject($dataTransferObject);
        $this->shipmentMethodRepository->add($shipmentMethod);
        $dataTransferObject->setShipmentMethod($shipmentMethod);
    }
}

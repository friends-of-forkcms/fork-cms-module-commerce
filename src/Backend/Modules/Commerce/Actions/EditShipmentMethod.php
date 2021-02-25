<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Exception;

/**
 * This edit action allows you to edit a specific shipment method.
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class EditShipmentMethod extends BackendBaseActionEdit
{
    public function execute(): void
    {
        parent::execute();

        // Set the shipment method id
        $name = str_replace('shipment_method_', '', $this->getRequest()->get('id'));

        $className = $this->getShipmentMethodAction($name);

        // First check if our class exists
        if (!class_exists($className)) {
            throw new Exception("Class {$name} is not found!");
        }

        // Load our class
        /**
         * @var \Backend\Modules\Commerce\ShipmentMethods\Base\Edit $shipmentMethod
         */
        $shipmentMethod = new $className();

        // Set some required parameters
        $shipmentMethod->setRequest($this->getRequest());
        $shipmentMethod->setName($name);
        $shipmentMethod->setTemplate($this->template);

        // Execute the shipment method
        $shipmentMethod->execute();

        // Set the required template
        $this->template = $shipmentMethod->getTemplate();
        $this->display($shipmentMethod->getTemplateName());
    }

    private function getShipmentMethodAction(string $name): string
    {
        return "\\Backend\\Modules\\Commerce\\ShipmentMethods\\{$name}\\Actions\\Edit";
    }
}

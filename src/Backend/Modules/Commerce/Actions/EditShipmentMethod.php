<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\ShipmentMethod\Exception\ShipmentMethodNotFound;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethod;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethodRepository;
use Exception;

/**
 * This edit action allows you to edit a specific shipment method.
 * This will proxy through to the underlying module's edit action and template!
 */
class EditShipmentMethod extends BackendBaseActionEdit
{
    public function execute(): void
    {
        parent::execute();

        $shipmentMethod = $this->getShipmentMethod();

        // Load class from the external module
        $className = $this->getShipmentMethodAction($shipmentMethod->getModule());
        if (!class_exists($className)) {
            throw new Exception("Class $className is not found!");
        }

        // Load our class and pass the rendered template as output content
        /**
         * @var \Backend\Modules\Commerce\Domain\ShipmentMethod\Edit $shipmentMethodEdit
         */
        $shipmentMethodEdit = new $className($this->getKernel());
        $shipmentMethodEdit->execute();
        $shipmentMethodEdit->display();
        $this->content = $shipmentMethodEdit->getContent()->getContent();
    }

    private function getShipmentMethodAction(string $moduleName): string
    {
        return "\\Backend\\Modules\\$moduleName\\Actions\\Edit";
    }

    protected function getShipmentMethod(): ShipmentMethod
    {
        /** @var ShipmentMethodRepository $shipmentMethodRepository */
        $shipmentMethodRepository = $this->get('commerce.repository.shipment_method');

        try {
            return $shipmentMethodRepository->findOneByIdAndLocale(
                $this->getRequest()->query->getInt('id'),
                Locale::workingLocale()
            );
        } catch (ShipmentMethodNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction('ShipmentMethods', null, null, $parameters);
    }
}

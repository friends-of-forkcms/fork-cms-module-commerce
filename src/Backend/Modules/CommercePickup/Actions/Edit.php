<?php

namespace Backend\Modules\CommercePickup\Actions;

use Backend\Core\Engine\Model;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\ShipmentMethod\Edit as ShipmentBaseActionEdit;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethod;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethodDataTransferObject;
use Backend\Modules\CommercePickup\Domain\Pickup\Command\UpdatePickupShipmentMethod;
use Backend\Modules\CommercePickup\Domain\Pickup\PickupShipmentMethodType;
use Symfony\Component\Form\Form;

class Edit extends ShipmentBaseActionEdit
{
    // Needed to override the module because we render this action inside the Commerce module.
    protected string $module = 'CommercePickup';

    public function execute(): void
    {
        parent::execute();

        $shipmentMethod = $this->getShipmentMethod();
        $form = $this->getForm($shipmentMethod);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());

            return;
        }

        $this->updateShipmentMethod($form);

        $this->redirect(
            Model::createUrlForAction(
                'ShipmentMethods',
                null,
                null,
                [
                    'report' => 'edited',
                    'var' => '',
                    'highlight' => 'row-' . $shipmentMethod->getId(),
                ]
            )
        );
    }

    private function getForm(ShipmentMethod $shipmentMethod): Form
    {
        $data = $this->shipmentMethodSettingsRepository->getShipmentMethodData(
            new UpdatePickupShipmentMethod($shipmentMethod, Locale::workingLocale())
        );
        $form = $this->createForm(
            PickupShipmentMethodType::class,
            $data,
            [
                'entityManager' => $this->entityManager,
            ]
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateShipmentMethod(Form $form): void
    {
        // Get the form data
        /** @var ShipmentMethodDataTransferObject $data */
        $data = $form->getData();

        // The command bus will handle the saving of the shipment method in the database.
        $this->commandBus->handle($data);
    }
}

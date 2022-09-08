<?php

namespace Backend\Modules\CommerceDelivery\Actions;

use Backend\Core\Engine\Model;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\ShipmentMethod\Edit as ShipmentBaseActionEdit;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethod;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethodDataTransferObject;
use Backend\Modules\CommerceDelivery\Domain\Delivery\Command\UpdateDeliveryShipmentMethod;
use Backend\Modules\CommerceDelivery\Domain\Delivery\DeliveryShipmentMethodType;
use Symfony\Component\Form\Form;

class Edit extends ShipmentBaseActionEdit
{
    // Needed to override the module because we render this action inside the Commerce module.
    protected string $module = 'CommerceDelivery';

    public function execute(): void
    {
        parent::execute();

        $shipmentMethod = $this->getShipmentMethod();

        $countries = [];
        foreach ($this->get('commerce.repository.country')->findAll() as $country) {
            $countries[] = [
                'id' => $country->getId(),
                'name' => $country->getName(),
            ];
        }

        $form = $this->getForm($shipmentMethod, $countries);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('countries', $countries);

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

    private function getForm(ShipmentMethod $shipmentMethod, $countries = []): Form
    {
        $data = $this->shipmentMethodSettingsRepository->getShipmentMethodData(
            new UpdateDeliveryShipmentMethod($shipmentMethod, Locale::workingLocale())
        );
        $form = $this->createForm(
            DeliveryShipmentMethodType::class,
            $data,
            [
                'entityManager' => $this->entityManager,
                'countries' => $countries,
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

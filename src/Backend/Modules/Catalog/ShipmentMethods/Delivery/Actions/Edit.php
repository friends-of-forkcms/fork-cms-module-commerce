<?php

namespace Backend\Modules\Catalog\ShipmentMethods\Delivery\Actions;

use Backend\Core\Engine\Model;
use Backend\Modules\Catalog\ShipmentMethods\Base\Edit as BaseEdit;
use Backend\Modules\Catalog\ShipmentMethods\Delivery\DeliveryDataTransferObject;
use Backend\Modules\Catalog\ShipmentMethods\Delivery\DeliveryType;
use Symfony\Component\Form\Form;

class Edit extends BaseEdit
{
    public function execute(): void
    {
        parent::execute();

        $form = $this->getForm();

        if ( ! $form->isSubmitted() || ! $form->isValid()) {
            $this->template->assign('form', $form->createView());
            return;
        }

        // Update our data
        $this->updateData($form);

        $this->redirect(
            Model::createUrlForAction(
                'ShipmentMethods',
                null,
                null,
                [
                    'report'    => 'edited',
                    'var'       => '',
                    'highlight' => $this->getDataGridRowKey(),
                ]
            )
        );
    }

    private function getForm(): Form
    {
        $form = $this->createForm(
            DeliveryType::class,
            $this->getData(new DeliveryDataTransferObject())
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    protected function updateData(Form $form): void
    {
        parent::updateData($form);

        // Get the form data
        $data = $form->getData();

        // Save the form data
        $this->saveSetting('name', $data->name);
        $this->saveSetting('price', (float) $data->price);
        $this->saveSetting('vat', $data->vat->getId());
    }
}

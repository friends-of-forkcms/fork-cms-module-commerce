<?php

namespace Backend\Modules\Catalog\ShipmentMethods\BasedOnWeight\Actions;

use Backend\Core\Engine\Model;
use Backend\Modules\Catalog\ShipmentMethods\Base\Edit as BaseEdit;
use Backend\Modules\Catalog\ShipmentMethods\BasedOnWeight\BasedOnWeight;
use Backend\Modules\Catalog\ShipmentMethods\BasedOnWeight\BasedOnWeightTransferObject;
use Symfony\Component\Form\Form;

class Edit extends BaseEdit
{
    public function execute(): void
    {
        parent::execute();

        $form = $this->getForm();

        if (!$form->isSubmitted() || !$form->isValid()) {
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
                    'report' => 'edited',
                    'var' => '',
                    'highlight' => $this->getDataGridRowKey(),
                ]
            )
        );
    }

    private function getForm(): Form
    {
        $form = $this->createForm(
            BasedOnWeight::class,
            $this->getData(new BasedOnWeightTransferObject())
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
        $this->saveSetting('values', $data->values);
        $this->saveSetting('vat', $data->vat->getId());
    }
}

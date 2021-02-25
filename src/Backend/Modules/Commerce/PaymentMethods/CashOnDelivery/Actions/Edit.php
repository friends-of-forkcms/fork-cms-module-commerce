<?php

namespace Backend\Modules\Commerce\PaymentMethods\CashOnDelivery\Actions;

use Backend\Core\Engine\Model;
use Backend\Modules\Commerce\PaymentMethods\Base\Edit as BaseEdit;
use Backend\Modules\Commerce\PaymentMethods\CashOnDelivery\CashOnDeliveryDataTransferObject;
use Backend\Modules\Commerce\PaymentMethods\CashOnDelivery\CashOnDeliveryType;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Form\Form;

class Edit extends BaseEdit
{
    /**
     * {@inheritdoc}
     */
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
                'PaymentMethods',
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
            CashOnDeliveryType::class,
            $this->getData(new CashOnDeliveryDataTransferObject()),
            [
                'entityManager' => $this->entityManager
            ]
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateData(Form $form): void
    {
        // Get the form data
        $data = $form->getData();

        // Save the form data
        $this->setData($form->getData(), true);

        // Install our payment method or not
        $this->installPaymentMethod($data->installed);
    }
}

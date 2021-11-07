<?php

namespace Backend\Modules\CommerceCashOnDelivery\Actions;

use Backend\Core\Engine\Model;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethod;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethodDataTransferObject;
use Backend\Modules\Commerce\Domain\PaymentMethod\Edit as PaymentBaseActionEdit;
use Backend\Modules\CommerceCashOnDelivery\Domain\CashOnDelivery\CashOnDeliveryPaymentMethodType;
use Backend\Modules\CommerceCashOnDelivery\Domain\CashOnDelivery\Command\UpdateCashOnDeliveryPaymentMethod;
use Symfony\Component\Form\Form;

class Edit extends PaymentBaseActionEdit
{
    // Needed to override the module because we render this action inside the Commerce module.
    protected string $module = 'CommerceCashOnDelivery';

    public function execute(): void
    {
        parent::execute();

        $paymentMethod = $this->getPaymentMethod();
        $form = $this->getForm($paymentMethod);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());

            return;
        }

        $this->updatePaymentMethod($form);

        $this->redirect(
            Model::createUrlForAction(
                'PaymentMethods',
                null,
                null,
                [
                    'report' => 'edited',
                    'var' => '',
                    'highlight' => 'row-' . $paymentMethod->getId(),
                ]
            )
        );
    }

    private function getForm(PaymentMethod $paymentMethod): Form
    {
        $form = $this->createForm(
            CashOnDeliveryPaymentMethodType::class,
            $this->getData(new UpdateCashOnDeliveryPaymentMethod($paymentMethod, Locale::workingLocale())),
            [
                'entityManager' => $this->entityManager,
            ]
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updatePaymentMethod(Form $form): void
    {
        // Get the form data
        /** @var PaymentMethodDataTransferObject $data */
        $data = $form->getData();

        // The command bus will handle the saving of the payment method in the database.
        $this->commandBus->handle($data);
    }
}

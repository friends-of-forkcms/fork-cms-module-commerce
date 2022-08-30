<?php

namespace Backend\Modules\CommerceMollie\Actions;

use Backend\Core\Engine\Model;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\PaymentMethod\Edit as PaymentBaseActionEdit;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethod;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethodDataTransferObject;
use Backend\Modules\CommerceMollie\Domain\PaymentMethod\Command\UpdateMolliePaymentMethod;
use Backend\Modules\CommerceMollie\Domain\PaymentMethod\MolliePaymentMethodDataTransferObject;
use Backend\Modules\CommerceMollie\Domain\PaymentMethod\MolliePaymentMethodType;
use Mollie\Api\MollieApiClient;
use Symfony\Component\Form\Form;

class Edit extends PaymentBaseActionEdit
{
    // Needed to override the module because we render this action inside the Commerce module.
    protected string $module = 'CommerceMollie';

    public function execute(): void
    {
        parent::execute();

        $paymentMethod = $this->getPaymentMethod();
        $data = $this->getData(new MolliePaymentMethodDataTransferObject($paymentMethod, Locale::workingLocale()));

        $enabledMethods = [];
        if ($data->apiKey) {
            $mollie = new MollieApiClient();
            $mollie->setApiKey($data->apiKey);

            foreach ($mollie->methods->allActive() as $method) {
                $enabledMethods[] = [
                    'id' => $method->id,
                    'description' => $method->description,
                ];
            }
        }

        $form = $this->getForm($paymentMethod, $enabledMethods);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('enabledMethods', $enabledMethods);

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

    private function getForm(PaymentMethod $paymentMethod, $enabledMethods = []): Form
    {
        $form = $this->createForm(
            MolliePaymentMethodType::class,
            $this->getData(new UpdateMolliePaymentMethod($paymentMethod, Locale::workingLocale())),
            [
                'entityManager' => $this->entityManager,
                'enabledMethods' => $enabledMethods,
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

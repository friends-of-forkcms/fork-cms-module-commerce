<?php

namespace Frontend\Modules\Commerce\CheckoutStep;

use Backend\Modules\Commerce\Domain\Order\ConfirmOrderDataTransferObject;
use Backend\Modules\Commerce\Domain\Order\ConfirmOrderType;
use Common\Uri;
use Frontend\Core\Language\Language;
use Frontend\Modules\Profiles\Engine\Authentication;
use Symfony\Component\Form\Form;

class ConfirmOrderStep extends Step
{
    public static string $stepIdentifier = 'confirmOrder';
    private Form $form;

    public function init(): void
    {
        $this->setStepName(Language::lbl('ConfirmOrder'));
        $this->complete = $this->session->has('confirm_order');
    }

    /**
     * @throws ChangeStepException
     */
    public function execute(): void
    {
        $this->form = $this->handleForm($this->getForm());

        if ($this->form->isSubmitted()) {
            if ($this->form->isValid()) {
                $this->goToNextStep();
            } else {
                $this->complete = false;
            }
        }
    }

    public function render(): string
    {
        $this->template->assign('form', $this->form->createView());

        if (Authentication::isLoggedIn()) {
            $this->template->assign(
                'addressUrl',
                $this->checkoutProgress->getUrlByIdentifier(AddressesStep::$stepIdentifier)
            );
        } else {
            $this->template->assign(
                'addressUrl',
                $this->checkoutProgress->getUrlByIdentifier(AccountStep::$stepIdentifier)
            );
        }

        $this->template->assign(
            'shipmentMethodUrl',
            $this->checkoutProgress->getUrlByIdentifier(ShipmentMethodStep::$stepIdentifier)
        );
        $this->template->assign(
            'paymentMethodUrl',
            $this->checkoutProgress->getUrlByIdentifier(PaymentMethodStep::$stepIdentifier)
        );

        return parent::render();
    }

    public function invalidateStep(): void
    {
        $this->session->remove('confirm_order');

        parent::invalidateStep();
    }

    private function handleForm(Form $form): Form
    {
        if ($form->isSubmitted() && $form->isValid()) {
            // Store data in session
            $this->session->set('confirm_order', $form->getNormData());
        }

        return $form;
    }

    private function getForm(): Form
    {
        $confirmOrderData = new ConfirmOrderDataTransferObject();
        $confirmOrderData->accept_terms_and_conditions = true;

        // Load our form
        $form = $this->createForm(
            ConfirmOrderType::class,
            $confirmOrderData
        );

        // Assign current request to form
        $form->handleRequest($this->getRequest());

        return $form;
    }

    public function getUrl(): ?string
    {
        return parent::getUrl() . '/' . Uri::getUrl(Language::lbl('ConfirmOrder'));
    }
}

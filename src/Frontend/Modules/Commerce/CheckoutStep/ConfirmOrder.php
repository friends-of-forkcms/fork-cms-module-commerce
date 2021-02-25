<?php

namespace Frontend\Modules\Commerce\CheckoutStep;

use Backend\Modules\Commerce\Domain\Order\ConfirmOrderDataTransferObject;
use Backend\Modules\Commerce\Domain\Order\ConfirmOrderType;
use Common\Uri;
use Frontend\Core\Language\Language;
use Frontend\Modules\Profiles\Engine\Authentication;
use Symfony\Component\Form\Form;

class ConfirmOrder extends Step
{
    public static $stepIdentifier = 'confirmOrder';

    /**
     * @var Form
     */
    private $form;

    public function init()
    {
        $this->setStepName(Language::lbl('ConfirmOrder'));

        $this->complete = $this->session->has('confirm_order');
    }

    /**
     * @throws ChangeStepException
     */
    public function execute(): void
    {
        $this->addJsFile('ConfirmOrder.js');

        $this->form = $this->handleForm($this->getForm());

        if ($this->form->isSubmitted()) {
            if ($this->form->isValid()) {
                $this->goToNextStep();
            } else {
                $this->complete = false;
            }
        }
    }

    public function render()
    {
        $this->template->assign('form', $this->form->createView());

        if (Authentication::isLoggedIn()) {
            $this->template->assign(
                'addressUrl',
                $this->checkoutProgress->getUrlByIdentifier(Addresses::$stepIdentifier)
            );
        } else {
            $this->template->assign(
                'addressUrl',
                $this->checkoutProgress->getUrlByIdentifier(Account::$stepIdentifier)
            );
        }

        $this->template->assign(
            'shipmentMethodUrl',
            $this->checkoutProgress->getUrlByIdentifier(ShipmentMethod::$stepIdentifier)
        );
        $this->template->assign(
            'paymentMethodUrl',
            $this->checkoutProgress->getUrlByIdentifier(PaymentMethod::$stepIdentifier)
        );

        return parent::render();
    }

    public function invalidateStep()
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

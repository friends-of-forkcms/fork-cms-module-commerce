<?php

namespace Frontend\Modules\Commerce\CheckoutStep;

use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Commerce\Domain\PaymentMethod\CheckoutPaymentMethodDataTransferObject;
use Backend\Modules\Commerce\Domain\PaymentMethod\CheckoutPaymentMethodType;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethodRepository;
use Backend\Modules\Commerce\PaymentMethods\Base\Checkout\Quote;
use Common\Uri;
use Frontend\Core\Language\Language;
use Frontend\Core\Language\Locale;
use Symfony\Component\Form\Form;

class PaymentMethod extends Step
{
    public static $stepIdentifier = 'paymentMethod';

    /**
     * @var Form
     */
    private $form;

    public function init()
    {
        $this->setStepName(Language::lbl('PaymentMethod'));

        if ($this->cart->getPaymentMethod()) {
            $this->complete = true;
        }
    }

    /**
     * @throws ChangeStepException
     */
    public function execute(): void
    {
        $paymentMethods = $this->getPaymentMethods();
        $this->form = $this->handleForm($this->getForm($paymentMethods), $paymentMethods);

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

        return parent::render();
    }

    /**
     * Get the guest form. Returns the path of the template.
     */
    private function handleForm(Form $form, $paymentMethods): Form
    {
        // Check if there are any errors in our submit
        if ($form->isSubmitted() && $form->isValid()) {
            $this->cart->setPaymentMethod($form->getNormData()->payment_method);
            $this->cart->setPaymentMethodData($paymentMethods[$form->getNormData()->payment_method]);
            $this->cart->calculateTotals();

            $this->getCartRepository()->save($this->cart);
        }

        return $form;
    }

    public function invalidateStep()
    {
        // Clear the payment method data because these are based on our shipment option
        $this->cart->setPaymentMethod(null);
        $this->cart->setPaymentMethodData(null);
        $this->cart->calculateTotals();

        $this->getCartRepository()->save($this->cart);

        parent::invalidateStep();
    }

    private function getForm($paymentMethods): Form
    {
        $formData = new CheckoutPaymentMethodDataTransferObject();
        $formData->payment_method = $this->cart->getPaymentMethod();

        // Load our form
        $form = $this->createForm(
            CheckoutPaymentMethodType::class,
            $formData,
            [
                'payment_methods' => $paymentMethods,
            ]
        );

        // Assign current request to form
        $form->handleRequest($this->getRequest());

        return $form;
    }

    /**
     * Get the payment methods to populate our form.
     */
    private function getPaymentMethods(): array
    {
        /**
         * @var PaymentMethodRepository $paymentMethodRepository
         */
        $paymentMethodRepository = $this->get('commerce.repository.payment_method');
        $availablePaymentMethods = $paymentMethodRepository->findInstalledPaymentMethods(Locale::frontendLanguage());

        $paymentMethods = [];
        foreach ($availablePaymentMethods as $paymentMethod) {
            $className = "\\Backend\\Modules\\Commerce\\PaymentMethods\\{$paymentMethod}\\Checkout\\Quote";

            /**
             * @var Quote $class
             */
            $class = new $className($paymentMethod, $this->cart, $this->getPaymentAddress());
            foreach ($class->getQuote() as $key => $options) {
                $paymentMethods[$paymentMethod.'.'.$key] = $options;
            }
        }

        return $paymentMethods;
    }

    private function getPaymentAddress(): OrderAddress
    {
        if ($this->cart->getInvoiceAddress()) {
            return $this->cart->getInvoiceAddress();
        }

        return $this->cart->getShipmentAddress();
    }

    public function getUrl(): ?string
    {
        return parent::getUrl().'/'.Uri::getUrl(Language::lbl('PaymentMethod'));
    }
}

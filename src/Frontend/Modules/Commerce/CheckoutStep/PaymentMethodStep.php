<?php

namespace Frontend\Modules\Commerce\CheckoutStep;

use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Commerce\Domain\PaymentMethod\Checkout\PaymentMethodQuote;
use Backend\Modules\Commerce\Domain\PaymentMethod\CheckoutPaymentMethodDataTransferObject;
use Backend\Modules\Commerce\Domain\PaymentMethod\CheckoutPaymentMethodType;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethodRepository;
use Backend\Modules\Commerce\Domain\Settings\CommerceModuleSettingsRepository;
use Common\Core\Model;
use Common\Uri;
use Frontend\Core\Language\Language;
use Frontend\Core\Language\Locale;
use Symfony\Component\Form\Form;

class PaymentMethodStep extends Step
{
    public static string $stepIdentifier = 'paymentMethod';
    private Form $form;

    public function init(): void
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
        // Clear previous payment methods on the cart because they are dependent on the shipping method!
        $this->nextStep->invalidateStep();

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

    public function render(): string
    {
        $this->template->assign('form', $this->form->createView());

        return parent::render();
    }

    /**
     * Get the guest form. Returns the path of the template.
     */
    private function handleForm(Form $form, $paymentMethods): Form
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $this->cart->setPaymentMethod($form->getNormData()->payment_method);
            $this->cart->setPaymentMethodData($paymentMethods[$form->getNormData()->payment_method]);
            $this->cart->calculateTotals();

            $this->getCartRepository()->save($this->cart);
        }

        return $form;
    }

    public function invalidateStep(): void
    {
        // Clear the payment method data because these are based on our shipment option
        $this->cart->setPaymentMethod(null);
        $this->cart->setPaymentMethodData(null);
        $this->cart->calculateTotals();

        $this->getCartRepository()->save($this->cart);

        parent::invalidateStep();
    }

    private function getForm(array $paymentMethods): Form
    {
        $formData = new CheckoutPaymentMethodDataTransferObject();

        // Pre-fill the payment method if available
        $paymentMethod = $this->cart->getPaymentMethod();
        if ($paymentMethod !== null) {
            $formData->payment_method = $paymentMethod;
        }

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
        /** @var PaymentMethodRepository $paymentMethodRepository */
        $paymentMethodRepository = $this->get('commerce.repository.payment_method');
        $availablePaymentMethods = $paymentMethodRepository->findEnabledPaymentMethods(Locale::frontendLanguage());
        $commerceModuleSettingsRepository = new CommerceModuleSettingsRepository(
            Model::get('fork.settings'),
            Locale::frontendLanguage()
        );

        $paymentMethods = [];
        foreach ($availablePaymentMethods as $paymentMethod) {
            $quoteClassName = $this->getPaymentMethodQuoteClass($paymentMethod->getModule());

            /** @var PaymentMethodQuote $class */
            $class = new $quoteClassName(
                $paymentMethod->getName(),
                $this->cart,
                $this->getPaymentAddress(),
                $commerceModuleSettingsRepository,
                $this->get('tbbc_money.formatter.money_formatter'),
                $this->get('commerce.repository.vat')
            );
            foreach ($class->getQuote() as $key => $options) {
                $paymentMethods[$paymentMethod->getModule() . '.' . $key] = $options;
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
        return parent::getUrl() . '/' . Uri::getUrl(Language::lbl('PaymentMethod'));
    }

    /**
     * We expect a Quote class to be implemented by the payment method module
     */
    private function getPaymentMethodQuoteClass(string $moduleName): string
    {
        $domainName = str_replace('Commerce', '', $moduleName);

        return "\\Backend\\Modules\\$moduleName\\Domain\\$domainName\\Checkout\\Quote";
    }
}

<?php

namespace Frontend\Modules\Commerce\CheckoutStep;

use Backend\Modules\Commerce\Domain\Settings\CommerceModuleSettingsRepository;
use Backend\Modules\Commerce\Domain\ShipmentMethod\Checkout\ShipmentMethodQuote;
use Backend\Modules\Commerce\Domain\ShipmentMethod\CheckoutShipmentMethodDataTransferObject;
use Backend\Modules\Commerce\Domain\ShipmentMethod\CheckoutShipmentMethodType;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethodRepository;
use Common\Uri;
use Frontend\Core\Language\Language;
use Frontend\Core\Language\Locale;
use Symfony\Component\Form\Form;

class ShipmentMethodStep extends Step
{
    public static string $stepIdentifier = 'shipmentMethod';
    private Form $form;

    public function init(): void
    {
        $this->setStepName(Language::lbl('ShipmentMethod'));

        if ($this->cart->getShipmentMethod()) {
            $this->complete = true;
        }
    }

    /**
     * @throws ChangeStepException
     */
    public function execute(): void
    {
        // Clear previous shipment methods on the cart!
        $this->nextStep->invalidateStep();

        $shipmentMethods = $this->getShipmentMethods();
        $this->form = $this->handleForm($this->getForm($shipmentMethods), $shipmentMethods);

        if ($this->form->isSubmitted()) {
            if ($this->form->isValid()) {
                $this->goToNextStep();
            } else {
                $this->complete = false;
            }
        }
    }

    public function isReachable(): bool
    {
        $reachable = parent::isReachable();

        if (!$this->cart->getShipmentAddress()) {
            $reachable = false;
        }

        return $reachable;
    }

    public function render(): string
    {
        $this->template->assign('form', $this->form->createView());

        return parent::render();
    }

    private function handleForm(Form $form, array $shipmentMethods): Form
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $this->cart->setShipmentMethod($form->getNormData()->shipment_method);
            $this->cart->setShipmentMethodData($shipmentMethods[$form->getNormData()->shipment_method]);
            $this->getCartRepository()->save($this->cart);

            $this->nextStep->invalidateStep();
        }

        return $form;
    }

    public function invalidateStep(): void
    {
        // Clear cart details
        $this->cart->setShipmentMethod(null);
        $this->cart->setShipmentMethodData(null);
        $this->getCartRepository()->save($this->cart);

        parent::invalidateStep();
    }

    private function getForm(array $shipmentMethods): Form
    {
        // Create new form or restore session
        $formData = new CheckoutShipmentMethodDataTransferObject();
        $formData->shipment_method = $this->cart->getShipmentMethod();

        // Load our form
        $form = $this->createForm(
            CheckoutShipmentMethodType::class,
            $formData,
            [
                'shipment_methods' => $shipmentMethods,
            ]
        );

        // Assign current request to form
        $form->handleRequest($this->getRequest());

        return $form;
    }

    /**
     * Get the shipment methods to populate our form.
     */
    private function getShipmentMethods(): array
    {
        $commerceModuleSettingsRepository = new CommerceModuleSettingsRepository($this->get('fork.settings'), Locale::frontendLanguage());
        /** @var ShipmentMethodRepository $shipmentMethodRepository */
        $shipmentMethodRepository = $this->get('commerce.repository.shipment_method');
        $availableShipmentMethods = $shipmentMethodRepository->findEnabledShipmentMethods(Locale::frontendLanguage());

        $shipmentMethods = [];
        foreach ($availableShipmentMethods as $shipmentMethod) {
            $quoteClassName = $this->getShipmentMethodQuoteClass($shipmentMethod->getModule());

            /** @var ShipmentMethodQuote $class */
            $class = new $quoteClassName(
                $shipmentMethod->getName(),
                $this->cart,
                $this->cart->getShipmentAddress(),
                $commerceModuleSettingsRepository,
                $this->get('tbbc_money.formatter.money_formatter'),
                $this->get('commerce.repository.vat')
            );
            foreach ($class->getQuote() as $key => $options) {
                $shipmentMethods[$shipmentMethod->getModule() . '.' . $key] = $options;
            }
        }

        return $shipmentMethods;
    }

    public function getUrl(): ?string
    {
        return parent::getUrl() . '/' . Uri::getUrl(Language::lbl('ShipmentMethod'));
    }

    /**
     * We expect a Quote class to be implemented by the shipping method module
     */
    private function getShipmentMethodQuoteClass(string $moduleName): string
    {
        $domainName = str_replace('Commerce', '', $moduleName);

        return "\\Backend\\Modules\\$moduleName\\Domain\\$domainName\\Checkout\\Quote";
    }
}

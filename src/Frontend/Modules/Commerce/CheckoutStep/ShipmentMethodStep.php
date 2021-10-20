<?php

namespace Frontend\Modules\Commerce\CheckoutStep;

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

    /**
     * @var Form
     */
    private $shipmentForm;

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
        $this->nextStep->invalidateStep();

        $this->shipmentForm = $this->handleForm($this->getForm());

        if ($this->shipmentForm->isSubmitted()) {
            if ($this->shipmentForm->isValid()) {
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
        $this->template->assign('form', $this->shipmentForm->createView());

        return parent::render();
    }

    private function handleForm(Form $form): Form
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $shipmentMethods = $this->getShipmentMethods();

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

    private function getForm(): Form
    {
        // Create new form or restore session
        $formData = new CheckoutShipmentMethodDataTransferObject();
        $formData->shipment_method = $this->cart->getShipmentMethod();

        // Load our form
        $form = $this->createForm(
            CheckoutShipmentMethodType::class,
            $formData,
            [
                'shipment_methods' => $this->getShipmentMethods(),
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
        /**
         * @var ShipmentMethodRepository $shipmentMethodRepository
         */
        $shipmentMethodRepository = $this->get('commerce.repository.shipment_method');
        $availableShipmentMethods = $shipmentMethodRepository->findInstalledShipmentMethods(Locale::frontendLanguage());

        $shipmentMethods = [];
        foreach ($availableShipmentMethods as $shipmentMethod) {
            $className = "\\Backend\\Modules\\Commerce\\ShipmentMethods\\{$shipmentMethod}\\Checkout\\Quote";

            /**
             * @var \Backend\Modules\Commerce\ShipmentMethods\Base\Checkout\Quote $class
             */
            $class = new $className($shipmentMethod, $this->cart, $this->cart->getShipmentAddress());
            foreach ($class->getQuote() as $key => $options) {
                $shipmentMethods[$shipmentMethod.'.'.$key] = $options;
            }
        }

        return $shipmentMethods;
    }

    public function getUrl(): ?string
    {
        return parent::getUrl().'/'.Uri::getUrl(Language::lbl('ShipmentMethod'));
    }
}

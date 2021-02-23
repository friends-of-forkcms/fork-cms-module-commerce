<?php

namespace Frontend\Modules\Commerce\Ajax;

use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\ShipmentMethod\CheckoutShipmentMethodDataTransferObject;
use Backend\Modules\Commerce\Domain\ShipmentMethod\CheckoutShipmentMethodType;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethodRepository;
use Backend\Modules\Commerce\ShipmentMethods\Base\Checkout\Quote;
use Common\Core\Model;
use Frontend\Core\Engine\Base\AjaxAction as FrontendBaseAJAXAction;
use Frontend\Core\Engine\TwigTemplate;
use Frontend\Core\Language\Locale;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ShipmentMethods extends FrontendBaseAJAXAction
{
    /**
     * @var bool
     */
    private $hasErrors = false;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * TwigTemplate instance
     *
     * @var TwigTemplate
     */
    protected $template;

    /**
     * @var string
     */
    private $nextStep;

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        parent::execute();

        $this->template = $this->getContainer()->get('templating');
        $this->session = Model::getSession();

        $this->parseShipmentMethods();
        $html = $this->template->getContent('Commerce/Layout/Templates/Checkout/ShipmentMethods.html.twig');

        $this->output(
            Response::HTTP_OK,
            [
                'html'      => $html,
                'hasErrors' => $this->hasErrors,
                'nextStep'  => $this->nextStep
            ]
        );
    }

    /**
     * Get the guest form. Returns the path of the template
     */
    private function parseShipmentMethods()
    {
        $form = $this->getForm();

        // Check if there are any errors in our submit
        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->hasErrors = true;
            } else {
                // Store data in session
                $shipmentMethods = $this->getShipmentMethods();
                $this->session->set(
                    'shipment_method',
                    [
                        'code' => $form->getNormData()->shipment_method,
                        'data' => $shipmentMethods[$form->getNormData()->shipment_method]
                    ]
                );
            }
        }

        // Invalid form
        $this->template->assign('form', $form->createView());

        // Choose our next step
        $this->nextStep = 'shipmentAddress';

        return true;
    }

    private function getForm(): Form
    {
        // Create new form or restore session
        $formData = new CheckoutShipmentMethodDataTransferObject();
        if ($this->session->get('shipment_method')) {
            $formData->shipment_method = $this->session->get('shipment_method')['code'];
        }

        // Load our form
        $form = $this->createForm(
            CheckoutShipmentMethodType::class,
            $formData,
            [
                'shipment_methods' => $this->getShipmentMethods()
            ]
        );

        // Assign current request to form
        $form->handleRequest($this->getRequest());

        return $form;
    }

    /**
     * Get the shipment methods to populate our form
     *
     * @return array
     */
    private function getShipmentMethods(): array
    {
        /**
         * @var ShipmentMethodRepository $shipmentMethodRepository
         */
        $shipmentMethodRepository = $this->get('commerce.repository.shipment_method');
        $availableShipmentMethods = $shipmentMethodRepository->findInstalledShipmentMethods(Locale::frontendLanguage());
        $cart = $this->getActiveCart();

        $shipmentMethods = [];
        foreach ($availableShipmentMethods as $shipmentMethod) {
            $className = "\\Backend\\Modules\\Commerce\\ShipmentMethods\\{$shipmentMethod}\\Checkout\\Quote";

            /**
             * @var Quote $class
             */
            $class = new $className($shipmentMethod, $cart, $this->session->get('guest_shipment_address'));
            foreach ($class->getQuote() as $key => $options) {
                $shipmentMethods[$shipmentMethod.'.'. $key] = $options;
            }
        }

        return $shipmentMethods;
    }

    /**
     * Get the active cart from the session
     *
     * @return Cart
     */
    private function getActiveCart(): Cart
    {
        $cartRepository = $this->get('commerce.repository.cart');
        $cookie = $this->get('fork.cookie');

        if (!$cartHash = $cookie->get('cart_hash')) {
            $cartHash = Uuid::uuid4();
            $cookie->set(
                'cart_hash',
                $cartHash,
                2592000,
                '/',
                null,
                null,
                true,
                false,
                SymfonyCookie::SAMESITE_NONE
            );
        }

        return $cartRepository->findBySessionId($cartHash, $this->getRequest()->getClientIp());
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string $type FQCN of the form type class i.e: MyClass::class
     * @param mixed $data The initial data for the form
     * @param array $options Options for the form
     *
     * @return Form
     */
    private function createForm(string $type, $data = null, array $options = []): Form
    {
        return $this->get('form.factory')->create($type, $data, $options);
    }
}

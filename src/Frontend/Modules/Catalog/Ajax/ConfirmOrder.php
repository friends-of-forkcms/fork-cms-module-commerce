<?php

namespace Frontend\Modules\Catalog\Ajax;

use Backend\Modules\Catalog\Domain\Cart\Cart;
use Backend\Modules\Catalog\Domain\Order\ConfirmOrderDataTransferObject;
use Backend\Modules\Catalog\Domain\Order\ConfirmOrderType;
use Backend\Modules\Catalog\Domain\Vat\Vat;
use Common\Core\Model;
use Frontend\Core\Engine\Base\AjaxAction as FrontendBaseAJAXAction;
use Frontend\Core\Engine\Navigation;
use Frontend\Core\Engine\TwigTemplate;
use Frontend\Core\Language\Locale;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ConfirmOrder extends FrontendBaseAJAXAction
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

        $this->parsePaymentMethods();
        $html = $this->template->getContent('Catalog/Layout/Templates/Checkout/ConfirmOrder.html.twig');

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
    private function parsePaymentMethods()
    {
        $form = $this->getForm();

        // Check if there are any errors in our submit
        if($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->hasErrors = true;
            }

            // Store data in session
            $this->session->set('confirm_order', $form->getNormData());
        }

        // Invalid form
        $this->template->assign('form', $form->createView());

        // Assign data to our view
        $cart = $this->getActiveCart();
        $vats = $cart->getVats();
        $shipmentMethod = $this->session->get('shipment_method');

        $cartTotal = $cart->getTotal() + $shipmentMethod['data']['price'];

        if ($shipmentMethod['data']['vat']) {
            $cartTotal += $shipmentMethod['data']['vat']['price'];

            if (!array_key_exists($shipmentMethod['data']['vat']['id'], $vats)) {
                $vat = $this->getVat($shipmentMethod['data']['vat']['id']);
                $vats[$vat->getId()] = [
                    'title' => $vat->getTitle(),
                    'total' => 0,
                ];
            }

            $vats[$shipmentMethod['data']['vat']['id']]['total'] += $shipmentMethod['data']['vat']['price'];
        }

        $this->template->assign('cart', $cart);
        $this->template->assign('shipmentMethod', $this->session->get('shipment_method'));
        $this->template->assign('cart_total', $cartTotal);
        $this->template->assign('vats', $vats);

        // Choose our next step
        $this->nextStep = Navigation::getUrlForBlock('Catalog', 'Cart') .'/store-order';

        return true;
    }

    private function getForm(): Form
    {
        // Load our form
        $form = $this->createForm(
            ConfirmOrderType::class,
            new ConfirmOrderDataTransferObject()
        );

        // Assign current request to form
        $form->handleRequest($this->getRequest());

        return $form;
    }

    /**
     * Get the active cart from the session
     *
     * @return Cart
     */
    private function getActiveCart(): Cart
    {
        $cartRepository = $this->get('catalog.repository.cart');
        $cookie = $this->get('fork.cookie');

        if (!$cartHash = $cookie->get('cart_hash')) {
            $cartHash = Uuid::uuid4();
            $cookie->set('cart_hash', $cartHash);
        }

        return $cartRepository->findBySessionId($cartHash, $this->getRequest()->getClientIp());
    }

    /**
     * Get a vat by its id
     *
     * @param int $id
     *
     * @return Vat
     */
    private function getVat(int $id): Vat
    {
        $vatRepository = $this->get('catalog.repository.vat');

        return $vatRepository->findOneByIdAndLocale($id, Locale::frontendLanguage());
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

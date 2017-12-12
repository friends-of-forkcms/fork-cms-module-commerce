<?php

namespace Frontend\Modules\Catalog\Ajax;

use Backend\Modules\Catalog\Domain\Cart\Cart;
use Backend\Modules\Catalog\Domain\PaymentMethod\CheckoutPaymentMethodDataTransferObject;
use Backend\Modules\Catalog\Domain\PaymentMethod\CheckoutPaymentMethodType;
use Backend\Modules\Catalog\Domain\PaymentMethod\PaymentMethodRepository;
use Backend\Modules\Catalog\PaymentMethods\Base\Checkout\Quote;
use Common\Core\Model;
use Frontend\Core\Engine\Base\AjaxAction as FrontendBaseAJAXAction;
use Frontend\Core\Engine\TwigTemplate;
use Frontend\Core\Language\Locale;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PaymentMethods extends FrontendBaseAJAXAction
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
        $html = $this->template->getContent('Catalog/Layout/Templates/Checkout/PaymentMethods.html.twig');

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
            $this->session->set('payment_method', $form->getNormData());
        }

        // Invalid form
        $this->template->assign('form', $form->createView());

        // Choose our next step
        $this->nextStep = 'paymentAddress';

        return true;
    }

    private function getForm(): Form
    {
        // Load our form
        $form = $this->createForm(
            CheckoutPaymentMethodType::class,
            new CheckoutPaymentMethodDataTransferObject(),
            [
                'payment_methods' => $this->getPaymentMethods()
            ]
        );

        // Assign current request to form
        $form->handleRequest($this->getRequest());

        return $form;
    }

    /**
     * Get the payment methods to populate our form
     *
     * @return array
     */
    private function getPaymentMethods(): array
    {
        /**
         * @var PaymentMethodRepository $paymentMethodRepository
         */
        $paymentMethodRepository = $this->get('catalog.repository.payment_method');
        $availablePaymentMethods = $paymentMethodRepository->findInstalledPaymentMethods(Locale::frontendLanguage());
        $cart = $this->getActiveCart();

        $paymentMethods = [];
        foreach ($availablePaymentMethods as $paymentMethod) {
            $className = "\\Backend\\Modules\\Catalog\\PaymentMethods\\{$paymentMethod}\\Checkout\\Quote";

            /**
             * @var Quote $class
             */
            $class = new $className($paymentMethod, $cart, $this->session->get('guest_address'));
            foreach ($class->getQuote() as $key => $options) {
                $paymentMethods[$paymentMethod.'.'. $key] = $options;
            }
        }

        return $paymentMethods;
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

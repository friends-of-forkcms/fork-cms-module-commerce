<?php

namespace Frontend\Modules\Catalog\Actions;

use Backend\Modules\Catalog\Domain\Cart\CartRepository;
use Backend\Modules\Catalog\Domain\Cart\Command\DeleteCart;
use Backend\Modules\Catalog\Domain\Order\Command\CreateOrder;
use Backend\Modules\Catalog\Domain\Order\Exception\OrderNotFound;
use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\Order\OrderRepository;
use Backend\Modules\Catalog\Domain\OrderProduct\Command\CreateOrderProduct;
use Backend\Modules\Catalog\Domain\OrderVat\Command\CreateOrderVat;
use Backend\Modules\Catalog\Domain\Quote\Event\QuoteCreated;
use Backend\Modules\Catalog\Domain\Quote\QuoteDataTransferObject;
use Backend\Modules\Catalog\Domain\Quote\QuoteType;
use Backend\Modules\Catalog\Domain\Vat\Vat;
use Backend\Modules\Catalog\PaymentMethods\Base\Checkout\ConfirmOrder;
use Common\Exception\RedirectException;
use Frontend\Core\Engine\Base\Block as FrontendBaseBlock;
use Frontend\Core\Engine\Model;
use Frontend\Core\Engine\Navigation;
use Frontend\Core\Language\Language;
use Frontend\Core\Language\Locale;

/**
 * This is the cart-action, it will display the cart
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Cart extends FrontendBaseBlock
{
    /**
     * Our current cart
     *
     * @var    \Backend\Modules\Catalog\Domain\Cart\Cart
     */
    private $cart;

    /**
     * Execute the action
     *
     * @throws RedirectException
     * @throws \Exception
     */
    public function execute(): void
    {
        parent::execute();

        $this->cart = $this->getCartRepository()->getActiveCart(false);
        $parameters = $this->url->getParameters(false);
        $parameterCount = count($parameters);

        if($parameterCount == 0) {
            $this->overview();
        } elseif ($parameterCount == 1) {
            if ($this->cart && $this->cart->getValues()->count() > 0) {
                switch ($this->url->getParameter(0)) {
                    case Language::lbl('Checkout'):
                        $this->isAllowedToCheckout();

                        $this->checkout();
                        break;
                    case Language::lbl('RequestQuoteUrl'):
                        $this->requestQuote();
                        break;
                    case Language::lbl('PaymentSuccessUrl'):
                        $this->isAllowedToCheckout();

                        $this->paymentSuccess();
                        break;
                    case Language::lbl('PaymentCancelledUrl'):
                        $this->isAllowedToCheckout();

                        $this->paymentCancelled();
                        break;
                    case 'store-order':
                        $this->isAllowedToCheckout();

                        $this->storeOrder();
                        break;
                    case 'post-payment':
                        $this->isAllowedToCheckout();

                        $this->postPayment();
                        break;
                    default:
                        $this->redirect(Navigation::getUrl(404));
                        break;
                }
            } elseif ($parameters[0] == 'webhook') {
                $this->runWebhook();
            } else {
                $this->redirect(Navigation::getUrl(404));
            }
        } else {
            $this->redirect(Navigation::getUrl(404));
        }
    }

    /**
     * Display the cart overview
     *
     * @return void
     */
    private function overview(): void
    {
        $this->loadTemplate();
        $this->template->assign('cart', $this->cart);

        $this->addJSData('isQuote', !$this->cart->isProductsInStock());
    }

    /**
     * Display the checkout page
     *
     * @return void
     */
    private function checkout(): void
    {
        $this->loadTemplate('Catalog/Layout/Templates/Checkout.html.twig');

        $this->addJS('Checkout.js');

        $this->header->setPageTitle(ucfirst(Language::lbl('Checkout')));

        $this->breadcrumb->addElement(
            ucfirst(Language::lbl('Checkout')),
            Navigation::getUrlForBlock('Catalog', 'Cart') .'/'. Language::lbl('Checkout')
        );
    }

    /**
     * Display the request quote page
     *
     * @throws RedirectException
     *
     * @return void
     */
    private function requestQuote(): void
    {
        $this->loadTemplate('Catalog/Layout/Templates/RequestQuote.html.twig');

        // Load the form
        $form = $this->createForm(
            QuoteType::class,
            new QuoteDataTransferObject()
        );

        // Assign current request to form
        $form->handleRequest($this->getRequest());

        // Check if there are any errors in our submit
        if(!$form->isSubmitted() || !$form->isValid()) {
            $this->breadcrumb->addElement(
                ucfirst(Language::lbl('RequestQuote')),
                Navigation::getUrlForBlock('Catalog', 'Cart') .'/'. Language::lbl('RequestQuoteUrl')
            );

            if ($this->getRequest()->get('submitted')) {
                $this->template->assign('quoteSubmitted', true);

                $this->header->setPageTitle(ucfirst(Language::lbl('ThankYouForYouQuote')));
                $this->loadTemplate('Catalog/Layout/Templates/QuoteSuccess.html.twig');

                $deleteCart = new DeleteCart($this->cart);
                $this->get('command_bus')->handle($deleteCart);
            } else {
                $this->header->setPageTitle(ucfirst(Language::lbl('RequestQuote')));

                $this->template->assign('form', $form->createView());
                $this->template->assign('cart', $this->cart);
                $this->template->assign('quoteSubmitted', false);
            }

            return;
        }

        $this->get('event_dispatcher')->dispatch(
            QuoteCreated::EVENT_NAME,
            new QuoteCreated($form->getData(), $this->cart)
        );

        $this->redirect(
            Navigation::getUrlForBlock('Catalog', 'Cart') .'/'. Language::lbl('RequestQuoteUrl') .'?submitted=1'
        );
    }

    /**
     * Display the success page
     *
     * @return void
     */
    private function paymentSuccess(): void
    {
        // Clear our session
        $session = Model::get('session');
        $session->remove('checkout_type');
        $session->remove('guest_address');
        $session->remove('guest_shipment_address');
        $session->remove('payment_method');
        $session->remove('shipment_method');

        $deleteCart = new DeleteCart($this->cart);
        $this->get('command_bus')->handle($deleteCart);

        $this->loadTemplate('Catalog/Layout/Templates/PaymentSuccess.html.twig');

        $this->header->setPageTitle(ucfirst(Language::lbl('ThankYouForYourOrder')));

        $this->breadcrumb->addElement(
            ucfirst(Language::lbl('Checkout')),
            Navigation::getUrlForBlock('Catalog', 'Cart') .'/'. Language::lbl('Checkout')
        );
    }

    /**
     * Display the error page
     *
     * @return void
     */
    private function paymentCancelled(): void
    {
        $this->loadTemplate('Catalog/Layout/Templates/PaymentCancelled.html.twig');

        $this->header->setPageTitle(ucfirst(Language::lbl('OrderCancelled')));

        $this->breadcrumb->addElement(
            ucfirst(Language::lbl('Checkout')),
            Navigation::getUrlForBlock('Catalog', 'Cart') .'/'. Language::lbl('Checkout')
        );
    }

    /**
     * Store our order and handle our payment method
     *
     * @throws \Exception
     *
     * @return void
     */
    private function storeOrder(): void
    {
        $session = Model::getSession();

        // Save the addresses
        $createAddress = $session->get('guest_address')->toCommand();
        $createShipmentAddress = $session->get('guest_shipment_address')->toCommand();

        $this->get('command_bus')->handle($createAddress);
        $this->get('command_bus')->handle($createShipmentAddress);

        $shipmentMethod = $session->get('shipment_method');

        // Recalculate order
        $this->cart->calculateTotals();

        // Some variables we need later
        $cartTotal = $this->cart->getTotal();
        $vats = $this->cart->getVats();

        // Add shipment method to order
        if ($shipmentMethod['data']['vat']) {
            $cartTotal += $shipmentMethod['data']['price'];
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

        // Order
        $createOrder = new CreateOrder();
        $createOrder->sub_total = $this->cart->getSubTotal();
        $createOrder->total = $cartTotal;
        $createOrder->invoiceAddress = $createAddress->getOrderAddressEntity();
        $createOrder->shipmentAddress = $createShipmentAddress->getOrderAddressEntity();
        $createOrder->shipment_method = $shipmentMethod['data']['name'];
        $createOrder->shipment_price = $shipmentMethod['data']['price'];

        $this->get('command_bus')->handle($createOrder);

        // Vats
        foreach ($vats as $vat) {
            $createOrderVat = new CreateOrderVat();
            $createOrderVat->title = $vat['title'];
            $createOrderVat->total = $vat['total'];
            $createOrderVat->order = $createOrder->getOrderEntity();

            $this->get('command_bus')->handle($createOrderVat);

            $createOrder->addVat($createOrderVat->getOrderVatEntity());
        }

        // Products
        foreach ($this->cart->getValues() as $product) {
            $createOrderProduct = new CreateOrderProduct();
            $createOrderProduct->sku = $product->getProduct()->getSku();
            $createOrderProduct->title = $product->getProduct()->getTitle();
            $createOrderProduct->price = $product->getProduct()->getPrice();
            $createOrderProduct->amount = $product->getQuantity();
            $createOrderProduct->total = $product->getTotal();
            $createOrderProduct->order = $createOrder->getOrderEntity();

            $this->get('command_bus')->handle($createOrderProduct);

            $createOrder->addProduct($createOrderProduct->getOrderProductEntity());
        }

        // Start the payment
        $paymentMethod = $this->getPaymentMethod($session->get('payment_method')->payment_method);
        $paymentMethod->setOrder($createOrder->getOrderEntity());
        $paymentMethod->setData($session->get('payment_method'));
        $paymentMethod->prePayment();
    }

    /**
     * Return from payment provider
     *
     * @throws RedirectException
     * @throws \Exception
     *
     * @return void
     */
    private function postPayment(): void
    {
        $session = Model::getSession();

        try {
            /** @var Order $order */
            $order = $this->getOrderRepository()->findOneById($this->getRequest()->get('order_id'));
        } catch (OrderNotFound $e) {
            $this->redirect(Navigation::getUrl(404));
        }

        // Start the payment
        $paymentMethod = $this->getPaymentMethod($session->get('payment_method')->payment_method);
        $paymentMethod->setOrder($order);
        $paymentMethod->setData($session->get('payment_method'));
        $paymentMethod->postPayment();
    }

    /**
     * Handle the webhook which is called by the payment provider.
     *
     * @throws \Exception
     *
     * @return void
     */
    private function runWebhook(): void
    {
        // Start the payment
        $paymentMethod = $this->getPaymentMethod($this->getRequest()->get('payment_method'));
        $paymentMethod->setRequest($this->getRequest());

        echo $paymentMethod->runWebhook();

        die();
    }

    /**
     * Get the order repository
     *
     * @return OrderRepository
     */
    private function getOrderRepository(): OrderRepository
    {
        return $this->get('catalog.repository.order');
    }

    /**
     * Get the cart repository
     *
     * @return CartRepository
     */
    private function getCartRepository(): CartRepository
    {
        return $this->get('catalog.repository.cart');
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
     * Get the payment method handler
     *
     * @param string $paymentMethod
     *
     * @throws \Exception
     *
     * @return ConfirmOrder
     */
    private function getPaymentMethod(string $paymentMethod): ConfirmOrder
    {
        $method = explode('.', $paymentMethod);

        if (count($method) != 2) {
            throw new \Exception('Invalid payment method');
        }

        $className = "\\Backend\\Modules\\Catalog\\PaymentMethods\\{$method[0]}\\Checkout\\ConfirmOrder";

        if (!class_exists($className)) {
            throw new \Exception('Class ' . $className . ' not found');
        }

        /**
         * @var ConfirmOrder $class
         */
        $class = new $className($method[0], $method[1]);

        return $class;
    }

    /**
     * Check if it is allowed to checkout
     *
     * @throws RedirectException
     */
    private function isAllowedToCheckout(): void
    {
        if (!$this->cart->isProductsInStock()) {
            $this->redirect(Navigation::getUrlForBlock('Catalog', 'Cart'));
        }
    }
}

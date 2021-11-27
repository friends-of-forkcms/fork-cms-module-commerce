<?php

namespace Frontend\Modules\Commerce\Actions;

use Backend\Modules\Commerce\Domain\Cart\Cart as CartEntity;
use Backend\Modules\Commerce\Domain\Cart\CartRepository;
use Backend\Modules\Commerce\Domain\Cart\Command\DeleteCart;
use Backend\Modules\Commerce\Domain\PaymentMethod\Checkout\ConfirmOrder;
use Backend\Modules\Commerce\Domain\Quote\Event\QuoteCreated;
use Backend\Modules\Commerce\Domain\Quote\QuoteDataTransferObject;
use Backend\Modules\Commerce\Domain\Quote\QuoteType;
use Common\Exception\ExitException;
use Common\Exception\RedirectException;
use Frontend\Core\Engine\Base\Block as FrontendBaseBlock;
use Frontend\Core\Engine\Navigation;
use Frontend\Core\Language\Language;
use Frontend\Modules\Commerce\CheckoutProgress;
use Frontend\Modules\Commerce\CheckoutStep\AccountStep;
use Frontend\Modules\Commerce\CheckoutStep\AddressesStep;
use Frontend\Modules\Commerce\CheckoutStep\ChangeStepException;
use Frontend\Modules\Commerce\CheckoutStep\ConfirmOrderStep;
use Frontend\Modules\Commerce\CheckoutStep\LoginStep;
use Frontend\Modules\Commerce\CheckoutStep\OrderPlacedStep;
use Frontend\Modules\Commerce\CheckoutStep\PaymentMethodStep;
use Frontend\Modules\Commerce\CheckoutStep\PayOrderStep;
use Frontend\Modules\Commerce\CheckoutStep\ShipmentMethodStep;
use Frontend\Modules\Profiles\Engine\Authentication;
use RuntimeException;

/**
 * This is the cart-action, it will display the cart.
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Cart extends FrontendBaseBlock
{
    private ?CartEntity $cart;

    public function execute(): void
    {
        parent::execute();

        $this->cart = $this->getCartRepository()->getActiveCart(false);
        $parameters = $this->url->getParameters(false);
        $parameterCount = count($parameters);

        if ($parameterCount === 0) {
            $this->overview();
        } elseif ($parameterCount === 1) {
            if ($this->cart && $this->cart->getValues()->count() > 0) {
                switch ($this->url->getParameter(0)) {
                    case Language::lbl('Checkout'):
                        $this->isAllowedToCheckout();
                        $this->checkout();

                        break;
                    case Language::lbl('RequestQuoteUrl'):
                        $this->requestQuote();

                        break;
                    default:
                        $this->redirect(Navigation::getUrl(404));

                        break;
                }
            } elseif ($parameters[0] === 'webhook') {
                throw new ExitException('', $this->runWebhook());
            } else {
                $this->redirect(Navigation::getUrl(404));
            }
        } elseif ($parameterCount === 2) {
            switch ($this->url->getParameter(0)) {
                case Language::lbl('Checkout'):
                    $this->isAllowedToCheckout();
                    $this->checkout();

                    break;
            }
        } else {
            $this->redirect(Navigation::getUrl(404));
        }
    }

    /**
     * Display the cart overview.
     */
    private function overview(): void
    {
        $this->loadTemplate();
        $this->template->assign('cart', $this->cart);

        $this->addJSData('isQuote', $this->cart ? !$this->cart->isProductsInStock() : false);
    }

    /**
     * Display the checkout page.
     *
     * @throws RedirectException
     */
    private function checkout(): void
    {
        $this->loadTemplate('Commerce/Layout/Templates/Checkout.html.twig');
        $this->header->setPageTitle(ucfirst(Language::lbl('Checkout')));

        $this->breadcrumb->addElement(
            ucfirst(Language::lbl('Checkout')),
            Navigation::getUrlForBlock($this->getModule(), 'Cart') . '/' . Language::lbl('Checkout')
        );

        $baseUrl = Navigation::getUrlForBlock($this->getModule(), 'Cart');

        $checkoutProgress = new CheckoutProgress();

        if (!Authentication::isLoggedIn()) {
            $checkoutProgress
                ->addStep(new LoginStep())
                ->addStep(new AccountStep());
        } else {
            $checkoutProgress->addStep(new AddressesStep());
        }

        $checkoutProgress
            ->addStep(new ShipmentMethodStep())
            ->addStep(new PaymentMethodStep())
            ->addStep(new ConfirmOrderStep())
            ->addStep(new PayOrderStep())
            ->addStep(new OrderPlacedStep());

        $urlParameters = $this->url->getParameters(false);
        $requestedUrl = $baseUrl . '/' . implode('/', $urlParameters);

        // Load the first step
        if (count($urlParameters) === 1) {
            $requestedUrl = $checkoutProgress->getFirstStep()->getUrl();
        }

        $currentStep = $checkoutProgress->getStepByUrl($requestedUrl);

        if ($currentStep === false) {
            $this->redirect($baseUrl);
        }

        if (!$currentStep->isReachable()) {
            $this->redirect($currentStep->getPreviousStep()->getUrl());
        }

        $checkoutProgress->setCurrentStep($currentStep);

        try {
            $currentStep->execute();
            foreach ($currentStep->getJsFiles() as $file) {
                $this->addJS('Checkout/' . $file);
            }
        } catch (ChangeStepException $exception) {
            $url = $exception->getStep()->getUrl();
            $this->redirect($url);
        }

        $this->template->assign('checkoutProgress', $checkoutProgress);
        $this->template->assign('currentStep', $currentStep);
    }

    /**
     * Display the request quote page.
     *
     * @throws RedirectException
     */
    private function requestQuote(): void
    {
        $this->loadTemplate('Commerce/Layout/Templates/RequestQuote.html.twig');

        // Load the form
        $form = $this->createForm(
            QuoteType::class,
            new QuoteDataTransferObject()
        );

        // Assign current request to form
        $form->handleRequest($this->getRequest());

        // Check if there are any errors in our submit
        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->breadcrumb->addElement(
                ucfirst(Language::lbl('RequestQuote')),
                Navigation::getUrlForBlock($this->getModule(), 'Cart') . '/' . Language::lbl('RequestQuoteUrl')
            );

            if ($this->getRequest()->get('submitted')) {
                $this->template->assign('quoteSubmitted', true);

                $this->header->setPageTitle(ucfirst(Language::lbl('ThankYouForYouQuote')));
                $this->loadTemplate('Commerce/Layout/Templates/QuoteSuccess.html.twig');

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
            Navigation::getUrlForBlock($this->getModule(), 'Cart') . '/' . Language::lbl('RequestQuoteUrl') . '?submitted=1'
        );
    }

    /**
     * Handle the webhook which is called by the payment provider.
     *
     * @throws \Exception
     */
    private function runWebhook(): string
    {
        // Start the payment
        $paymentMethod = $this->getPaymentMethod($this->getRequest()->get('payment_method'));
        $paymentMethod->setRequest($this->getRequest());

        return $paymentMethod->runWebhook();
    }

    /**
     * Get the cart repository.
     */
    private function getCartRepository(): CartRepository
    {
        return $this->get('commerce.repository.cart');
    }

    /**
     * Get the payment method handler.
     */
    private function getPaymentMethod(string $paymentMethod): ConfirmOrder
    {
        $method = explode('.', $paymentMethod);

        if (count($method) !== 2) {
            throw new RuntimeException('Invalid payment method');
        }

        $className = "\\Backend\\Modules\\Commerce\\PaymentMethods\\{$method[0]}\\Checkout\\ConfirmOrder";

        if (!class_exists($className)) {
            throw new RuntimeException('Class ' . $className . ' not found');
        }

        /** @var ConfirmOrder $class */
        $class = new $className($method[0], $method[1]);

        return $class;
    }

    /**
     * Check if it is allowed to checkout.
     *
     * @throws RedirectException
     */
    private function isAllowedToCheckout(): void
    {
        if (!$this->cart || !$this->cart->isProductsInStock()) {
            $this->redirect(Navigation::getUrlForBlock($this->getModule(), 'Cart'));
        }
    }
}

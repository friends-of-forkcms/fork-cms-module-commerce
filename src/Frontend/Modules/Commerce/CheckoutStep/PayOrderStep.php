<?php

namespace Frontend\Modules\Commerce\CheckoutStep;

use Backend\Modules\Commerce\Domain\Cart\CartValue;
use Backend\Modules\Commerce\Domain\CartRule\Command\UpdateCartRule;
use Backend\Modules\Commerce\Domain\Order\Command\CreateOrder;
use Backend\Modules\Commerce\Domain\Order\Command\UpdateOrder;
use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\OrderProduct\Command\CreateOrderProduct;
use Backend\Modules\Commerce\Domain\OrderProductNotification\OrderProductNotification;
use Backend\Modules\Commerce\Domain\OrderProductOption\OrderProductOption;
use Backend\Modules\Commerce\Domain\OrderRule\Command\CreateOrderRule;
use Backend\Modules\Commerce\Domain\OrderVat\Command\CreateOrderVat;
use Backend\Modules\Commerce\Domain\PaymentMethod\CheckoutPaymentMethodDataTransferObject;
use Backend\Modules\Commerce\Domain\PaymentMethod\Exception\PaymentMethodNotFound;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\Commerce\PaymentMethods\Base\Checkout\ConfirmOrder;
use Common\Exception\RedirectException;
use Common\Uri;
use Frontend\Core\Language\Language;
use Frontend\Core\Language\Locale;

class PayOrderStep extends Step
{
    public static string $stepIdentifier = 'payOrder';

    public function init(): void
    {
        $this->setStepName(Language::lbl('Pay'));

        if ($this->cart->getOrder()) {
            $this->complete = true;
        }
    }

    public function isComplete(): bool
    {
        if ($this->getNextStep()->isCurrent()) {
            return true;
        }

        return $this->complete && $this->getPreviousStep()->isComplete();
    }

    /**
     * @throws ChangeStepException
     * @throws RedirectException
     */
    public function execute(): void
    {
        if ($this->getRequest()->query->get('redirect') === 'postPayment') {
            $this->postPayment();

            return;
        }

        $this->prePayment();
    }

    /**
     * Before going to the payment provider.
     *
     * @throws RedirectException
     */
    private function prePayment(): void
    {
        $order = $this->createOrder();

        // Start the payment
        $paymentMethod = $this->getPaymentMethodHandler($this->cart->getPaymentMethod());
        $paymentMethod->setOrder($order);
        $paymentMethod->setData($this->getPaymentMethodDataTransferObject());
        $paymentMethod->setRedirectUrl($this->getUrl() . '?redirect=postPayment');

        $paymentMethod->prePayment();
    }

    /**
     * Return from payment provider.
     *
     * @throws \Exception
     * @throws RedirectException
     */
    private function postPayment(): void
    {
        // Start the payment
        $paymentMethod = $this->getPaymentMethodHandler($this->cart->getPaymentMethod());
        $paymentMethod->setOrder($this->cart->getOrder());
        $paymentMethod->setData($this->getPaymentMethodDataTransferObject());
        $paymentMethod->postPayment();

        if ($paymentMethod->isExpired()) {
            $this->flashError(Language::getMessage('PaymentExpired'));
            $this->goToPreviousStep();
        }

        if ($paymentMethod->isCanceled() || $paymentMethod->isFailed()) {
            $this->flashError(Language::getMessage('PaymentCancelled'));
            $this->goToPreviousStep();
        }

        if ($paymentMethod->isOpen() || $paymentMethod->isPaid()) {
            $this->updateCartRules();

            $this->goToNextStep();
        }
    }

    private function createOrder(): Order
    {
        // Recalculate order
        $this->cart->calculateTotals();

        // Some variables we need later
        $cartTotal = $this->cart->getTotal();
        $vats = $this->cart->getVats();

        // Order
        if ($this->cart->getOrder()) {
            $order = new UpdateOrder($this->cart->getOrder());

            $entityManager = $this->get('doctrine.orm.entity_manager');

            foreach ($order->products as $orderProduct) {
                $entityManager->remove($orderProduct);
            }

            foreach ($order->vats as $vat) {
                $entityManager->remove($vat);
            }

            foreach ($order->rules as $orderRule) {
                $entityManager->remove($orderRule);
            }

            $entityManager->flush();
        } else {
            $order = new CreateOrder();
            $order->cart = $this->cart;
            $order->account = $this->cart->getAccount();
        }

        $order->sub_total = $this->cart->getSubTotal();
        $order->total = $cartTotal;
        $order->paymentMethod = $this->cart->getPaymentMethodData()['label'];
        $order->invoiceAddress = ($this->cart->getInvoiceAddress() ? $this->cart->getInvoiceAddress() : $this->cart->getShipmentAddress());
        $order->shipmentAddress = $this->cart->getShipmentAddress();
        $order->shipment_method = $this->cart->getShipmentMethodData()['name'];
        $order->shipment_price = $this->cart->getShipmentMethodData()['price'];
        $this->get('command_bus')->handle($order);

        // Vats
        foreach ($vats as $vat) {
            $createOrderVat = new CreateOrderVat();
            $createOrderVat->title = $vat['title'];
            $createOrderVat->total = $vat['total'];
            $createOrderVat->order = $order->getOrderEntity();

            $this->get('command_bus')->handle($createOrderVat);

            $order->addVat($createOrderVat->getOrderVatEntity());
        }

        // Products
        /** @var CartValue $orderProduct */
        foreach ($this->cart->getValues() as $orderProduct) {
            $product = $orderProduct->getProduct();

            $createOrderProduct = new CreateOrderProduct();
            $createOrderProduct->product = $orderProduct->getProduct();
            $createOrderProduct->type = $product->getType();
            $createOrderProduct->sku = $product->getSku();
            $createOrderProduct->title = $product->getTitle();
            $createOrderProduct->width = $orderProduct->getWidth();
            $createOrderProduct->height = $orderProduct->getHeight();
            $createOrderProduct->order_width = $orderProduct->getOrderWidth();
            $createOrderProduct->order_height = $orderProduct->getOrderHeight();
            $createOrderProduct->price = $orderProduct->getPrice();
            $createOrderProduct->amount = $orderProduct->getQuantity();
            $createOrderProduct->total = $orderProduct->getTotal();
            $createOrderProduct->order = $order->getOrderEntity();

            // Add the options
            foreach ($orderProduct->getCartValueOptions() as $valueOption) {
                $productOption = new OrderProductOption();
                $productOption->setTitle($valueOption->getName());
                $productOption->setValue($valueOption->getValue());
                $productOption->setPrice($valueOption->getPrice());
                $productOption->setTotal($valueOption->getTotal());

                if ($valueOption->getProductOptionValue()) {
                    $productOption->setSku($valueOption->getProductOptionValue()->getSku());
                }

                $createOrderProduct->addProductOption($productOption);
            }

            // Add notifications
            $notifications = $product->getAllDimensionNotificationsByDimension(
                $orderProduct->getWidth(),
                $orderProduct->getHeight()
            );

            foreach ($notifications as $notification) {
                $productNotification = new OrderProductNotification();
                $productNotification->setMessage($notification->getMessage());

                $createOrderProduct->addProductNotification($productNotification);
            }

            $this->get('command_bus')->handle($createOrderProduct);

            $order->addProduct($createOrderProduct->getOrderProductEntity());
        }

        // Order rules
        foreach ($order->rules as $rule) {
            $order->removeRule($rule);
        }

        foreach ($this->cart->getCartRules() as $cartRule) {
            $createOrderRule = new CreateOrderRule();
            $createOrderRule->order = $order->getOrderEntity();
            $createOrderRule->cartRule = $cartRule;
            $createOrderRule->title = $cartRule->getTitle();
            $createOrderRule->code = $cartRule->getCode();
            if ($cartRule->getReductionPercentage()) {
                $createOrderRule->value = $cartRule->getReductionPercentage() . '% ' . Language::lbl('Discount');
            } else {
                $createOrderRule->value = '&euro; -' . number_format($cartRule->getReductionPrice(), 2, ',', '.');
            }
            $createOrderRule->total = $this->cart->getCartRuleTotal($cartRule);

            $this->get('command_bus')->handle($createOrderRule);

            $order->addRule($createOrderRule->getOrderRuleEntity());
        }

        return $order->getOrderEntity();
    }

    /**
     * Extract quantity from cart rules because the order is paid.
     */
    private function updateCartRules()
    {
        foreach ($this->cart->getOrder()->getRules() as $rule) {
            $updateCartRule = new UpdateCartRule($rule->getCartRule());
            $updateCartRule->quantity -= 1; // Cart rules cant be double

            $this->get('command_bus')->handle($updateCartRule);
        }
    }

    public function render(): string
    {
        return '';
    }

    public function getUrl(): ?string
    {
        return parent::getUrl() . '/' . Uri::getUrl(Language::lbl('PayOrder'));
    }

    private function getPaymentMethodDataTransferObject(): CheckoutPaymentMethodDataTransferObject
    {
        $data = new CheckoutPaymentMethodDataTransferObject();
        foreach ($this->cart->getPaymentMethodData() as $key => $value) {
            $data->$key = $value;
        }

        return $data;
    }

    /**
     * Get the payment method handler
     */
    private function getPaymentMethodHandler(string $paymentMethod): ConfirmOrder
    {
        [$moduleName, $optionName] = explode('.', $paymentMethod);

        $confirmOrderClassName = $this->getPaymentMethodConfirmOrderClass($moduleName);
        if (!class_exists($confirmOrderClassName)) {
            throw new PaymentMethodNotFound('Class ' . $confirmOrderClassName . ' not found');
        }

        return new $confirmOrderClassName($moduleName, $optionName);
    }

    /**
     * Get a vat by its id.
     */
    private function getVat(int $id): Vat
    {
        $vatRepository = $this->get('commerce.repository.vat');

        return $vatRepository->findOneByIdAndLocale($id, Locale::frontendLanguage());
    }

    /**
     * We expect a ConfirmOrder class to be implemented by the payment method module
     */
    private function getPaymentMethodConfirmOrderClass($moduleName): string
    {
        $domainName = str_replace('Commerce', '', $moduleName);

        return "\\Backend\\Modules\\$moduleName\\Domain\\$domainName\\Checkout\\ConfirmOrder";
    }
}

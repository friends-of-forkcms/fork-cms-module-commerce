<?php

namespace Backend\Modules\Catalog\PaymentMethods\Mollie\Checkout;

use Backend\Modules\Catalog\Domain\Order\Event\OrderCreated;
use Backend\Modules\Catalog\Domain\Order\Event\OrderUpdated;
use Backend\Modules\Catalog\Domain\Order\Exception\OrderNotFound;
use Backend\Modules\Catalog\Domain\OrderHistory\Command\CreateOrderHistory;
use Backend\Modules\Catalog\PaymentMethods\Base\Checkout\ConfirmOrder as BaseConfirmOrder;
use Frontend\Core\Engine\Navigation;

class ConfirmOrder extends BaseConfirmOrder
{
    /**
     * {@inheritdoc}
     */
    public function prePayment(): void
    {
        $mollie = new \Mollie_API_Client();
        $mollie->setApiKey($this->getSetting('apiKey'));

        // Create a new payment
        $payment = $mollie->payments->create(
            [
                'amount' => $this->order->getTotal(),
                'description' => 'Order '. $this->order->getId(),
                'redirectUrl' => SITE_URL . Navigation::getUrlForBlock('Catalog', 'Cart') .'/post-payment?order_id=' . $this->order->getId(),
                'webhookUrl' => SITE_URL . Navigation::getUrlForBlock('Catalog', 'Webhook') .'?order_id=' . $this->order->getId(),
                'method' => \Mollie_API_Object_Method::IDEAL,
                'issuer' => $this->data->issuer,
            ]
        );

        // Store the payment id
        $this->entityManager->getConnection()
            ->insert(
                'catalog_orders_mollie_payments',
                [
                    'order_id' => $this->order->getId(),
                    'transaction_id' => $payment->id
                ]
            );

        // Update the order history
        $createOrderHistory = new CreateOrderHistory();
        $createOrderHistory->order = $this->order;
        $createOrderHistory->orderStatus = $this->getOrderStatus($this->getSetting('orderInitId'));
        $createOrderHistory->message = 'Mollie order intialized with payment url: '. $payment->getPaymentUrl();
        $createOrderHistory->notify = true;
        $this->commandBus->handle($createOrderHistory);

        // Trigger an event to notify or not
        $this->eventDispatcher->dispatch(
            OrderCreated::EVENT_NAME,
            new OrderCreated($this->order, $createOrderHistory->getOrderHistoryEntity())
        );

        // Lets collect some money
        $this->redirect($payment->getPaymentUrl());
    }

    /**
     * {@inheritdoc}
     */
    public function postPayment(): void
    {
        $query = $this->entityManager->getConnection()->prepare(
            'SELECT order_id, method, transaction_id FROM catalog_orders_mollie_payments WHERE order_id = :order_id'

        );
        $query->bindValue('order_id', $this->order->getId());
        $query->execute();

        $result = $query->fetch(\PDO::FETCH_ASSOC);

        $mollie = new \Mollie_API_Client();
        $mollie->setApiKey($this->getSetting('apiKey'));

        $payment = $mollie->payments->get($result['transaction_id']);

        $order = null;
        try {
            $order = $this->getOrder($result['order_id']);
        } catch (OrderNotFound $e) {
            $this->goToErrorPage();
        }

        if ($payment->isPaid()) {
            $createOrderHistory = new CreateOrderHistory();
            $createOrderHistory->order = $order;
            $createOrderHistory->orderStatus = $this->getOrderStatus($this->getSetting('orderCompletedId'));
            $createOrderHistory->message = 'Order payed: ' . $payment->paidDatetime;
            $createOrderHistory->notify = true;
            $this->commandBus->handle($createOrderHistory);

            // Trigger an event to notify or not
            $this->eventDispatcher->dispatch(
                OrderUpdated::EVENT_NAME,
                new OrderUpdated($this->order, $createOrderHistory->getOrderHistoryEntity())
            );

            $this->goToSuccessPage();
        }

        if ($payment->isOpen()) {
            $this->goToSuccessPage();
        }

        if ($payment->isExpired() || $payment->isCancelled()) {
            $createOrderHistory = new CreateOrderHistory();
            $createOrderHistory->order = $order;
            $createOrderHistory->orderStatus = $this->getOrderStatus($this->getSetting('orderCancelledId'));
            $createOrderHistory->message = 'Order cancelled: ' . $payment->cancelledDatetime;
            $createOrderHistory->notify = true;
            $this->commandBus->handle($createOrderHistory);

            // Trigger an event to notify or not
            $this->eventDispatcher->dispatch(
                OrderUpdated::EVENT_NAME,
                new OrderUpdated($this->order, $createOrderHistory->getOrderHistoryEntity())
            );

            $this->goToCancelledPage();
        }
    }
}

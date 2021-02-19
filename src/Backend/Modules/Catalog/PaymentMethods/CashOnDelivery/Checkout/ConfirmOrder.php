<?php

namespace Backend\Modules\Catalog\PaymentMethods\CashOnDelivery\Checkout;

use Backend\Modules\Catalog\Domain\Order\Event\OrderCreated;
use Backend\Modules\Catalog\Domain\OrderHistory\Command\CreateOrderHistory;
use Backend\Modules\Catalog\PaymentMethods\Base\Checkout\ConfirmOrder as BaseConfirmOrder;

class ConfirmOrder extends BaseConfirmOrder
{
    /**
     * {@inheritdoc}
     */
    public function prePayment(): void
    {
        // Update the order history
        $createOrderHistory = new CreateOrderHistory();
        $createOrderHistory->order = $this->order;
        $createOrderHistory->orderStatus = $this->getOrderStatus($this->getSetting('orderInitId'));
        $this->commandBus->handle($createOrderHistory);

        // Trigger an event to notify or not
        $this->eventDispatcher->dispatch(
            OrderCreated::EVENT_NAME,
            new OrderCreated($this->order, $createOrderHistory->getOrderHistoryEntity())
        );

        $this->redirect($this->redirectUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function postPayment(): void
    {
        $this->paid = true;
    }
}

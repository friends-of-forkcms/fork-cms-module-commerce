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
        $createOrderHistory->notify = true;
        $this->commandBus->handle($createOrderHistory);

        // Trigger an event to notify or not
        $this->eventDispatcher->dispatch(
            OrderCreated::EVENT_NAME,
            new OrderCreated($this->order, $createOrderHistory->getOrderHistoryEntity())
        );

        $this->goToPostPaymentPage();
    }

    /**
     * {@inheritdoc}
     */
    public function postPayment(): void
    {
        $this->goToSuccessPage();
    }
}

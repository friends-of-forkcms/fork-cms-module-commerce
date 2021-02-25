<?php

namespace Backend\Modules\Commerce\Domain\Order\EventListener;

use Backend\Modules\Commerce\Domain\Order\Event\OrderCreated as OrderCreatedEvent;

final class OrderCreated extends OrderListener
{
    public function onOrderCreated(OrderCreatedEvent $event): void
    {
        $this->order = $event->getOrder();
        $this->orderHistory = $event->getOrderHistory();

        $this->sendCustomerEmail();
        $this->sendCompanyEmail();
    }
}

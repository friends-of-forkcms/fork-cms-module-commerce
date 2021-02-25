<?php

namespace Backend\Modules\Commerce\Domain\Order\EventListener;

use Backend\Modules\Commerce\Domain\Order\Event\OrderUpdated as OrderUpdatedEvent;

final class OrderUpdated extends OrderListener
{
    public function onOrderUpdated(OrderUpdatedEvent $event): void
    {
        $this->order = $event->getOrder();
        $this->orderHistory = $event->getOrderHistory();

        $this->sendCustomerEmail();
        $this->sendCompanyEmail();
    }
}

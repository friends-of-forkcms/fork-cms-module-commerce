<?php

namespace Backend\Modules\Catalog\Domain\Order\Event;

use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\OrderHistory\OrderHistory;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    /** @var Order */
    private $order;

    /** @var OrderHistory */
    private $orderHistory;

    public function __construct(Order $order, OrderHistory $orderHistory)
    {
        $this->order = $order;
        $this->orderHistory = $orderHistory;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getOrderHistory(): OrderHistory
    {
        return $this->orderHistory;
    }
}

<?php

namespace Backend\Modules\Catalog\Domain\OrderHistory\Event;

use Backend\Modules\Catalog\Domain\OrderHistory\OrderHistory;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    /** @var OrderHistory */
    private $orderHistory;

    public function __construct(OrderHistory $orderHistory)
    {
        $this->orderHistory = $orderHistory;
    }

    public function getOrderHistory(): OrderHistory
    {
        return $this->orderHistory;
    }
}

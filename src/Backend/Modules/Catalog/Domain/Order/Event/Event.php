<?php

namespace Backend\Modules\Catalog\Domain\Order\Event;

use Backend\Modules\Catalog\Domain\Order\Order;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    /** @var Order */
    private $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }
}

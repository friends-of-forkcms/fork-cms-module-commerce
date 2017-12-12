<?php

namespace Backend\Modules\Catalog\Domain\OrderStatus\Event;

use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatus;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    /** @var OrderStatus */
    private $orderStatus;

    public function __construct(OrderStatus $orderStatus)
    {
        $this->vat = $orderStatus;
    }

    public function getOrderStatus(): OrderStatus
    {
        return $this->vat;
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\OrderAddress\Event;

use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    /** @var OrderAddress */
    private $order_address;

    public function __construct(OrderAddress $order_address)
    {
        $this->order_address = $order_address;
    }

    public function getOrderAddress(): OrderAddress
    {
        return $this->order_address;
    }
}

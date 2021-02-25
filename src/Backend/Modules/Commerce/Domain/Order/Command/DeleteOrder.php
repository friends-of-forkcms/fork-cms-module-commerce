<?php

namespace Backend\Modules\Commerce\Domain\Order\Command;

use Backend\Modules\Commerce\Domain\Order\Order;

final class DeleteOrder
{
    /** @var Order */
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}

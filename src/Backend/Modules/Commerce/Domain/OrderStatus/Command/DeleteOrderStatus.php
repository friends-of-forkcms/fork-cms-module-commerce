<?php

namespace Backend\Modules\Commerce\Domain\OrderStatus\Command;

use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus;

final class DeleteOrderStatus
{
    public OrderStatus $orderStatus;

    public function __construct(OrderStatus $orderStatus)
    {
        $this->vat = $orderStatus;
    }
}

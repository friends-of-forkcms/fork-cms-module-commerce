<?php

namespace Backend\Modules\Commerce\Domain\OrderAddress\Command;

use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;

final class DeleteOrderAddress
{
    public OrderAddress $orderAddress;

    public function __construct(OrderAddress $orderAddress)
    {
        $this->orderAddress = $orderAddress;
    }
}

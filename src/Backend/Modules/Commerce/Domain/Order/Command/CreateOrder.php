<?php

namespace Backend\Modules\Commerce\Domain\Order\Command;

use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\Order\OrderDataTransferObject;

final class CreateOrder extends OrderDataTransferObject
{
    public function setOrderEntity(Order $order): void
    {
        $this->orderEntity = $order;
    }
}

<?php

namespace Backend\Modules\Catalog\Domain\Order\Command;

use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\Order\OrderDataTransferObject;

final class CreateOrder extends OrderDataTransferObject
{
    public function setOrderEntity(Order $order): void
    {
        $this->orderEntity = $order;
    }
}

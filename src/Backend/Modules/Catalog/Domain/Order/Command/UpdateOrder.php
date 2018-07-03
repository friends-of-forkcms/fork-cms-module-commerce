<?php

namespace Backend\Modules\Catalog\Domain\Order\Command;

use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\Order\OrderDataTransferObject;

final class UpdateOrder extends OrderDataTransferObject
{
    public function setOrderEntity(Order $orderEntity): void
    {
        $this->orderEntity = $orderEntity;
    }
}

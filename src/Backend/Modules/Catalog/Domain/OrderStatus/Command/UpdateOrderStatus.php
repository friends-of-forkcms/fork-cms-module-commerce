<?php

namespace Backend\Modules\Catalog\Domain\OrderStatus\Command;

use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatus;
use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatusDataTransferObject;

final class UpdateOrderStatus extends OrderStatusDataTransferObject
{
    public function __construct(OrderStatus $orderStatus)
    {
        parent::__construct($orderStatus);
    }

    public function setOrderStatusEntity(OrderStatus $orderStatusEntity): void
    {
        $this->vatEntity = $orderStatusEntity;
    }
}

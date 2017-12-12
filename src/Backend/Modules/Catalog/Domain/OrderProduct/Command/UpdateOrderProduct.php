<?php

namespace Backend\Modules\Catalog\Domain\OrderProduct\Command;

use Backend\Modules\Catalog\Domain\OrderProduct\OrderProduct;
use Backend\Modules\Catalog\Domain\OrderProduct\OrderProductDataTransferObject;

final class UpdateOrderProduct extends OrderProductDataTransferObject
{
    public function __construct(OrderProduct $orderProduct)
    {
        parent::__construct($orderProduct);
    }

    public function setOrderProductEntity(OrderProduct $orderProductEntity): void
    {
        $this->orderProductEntity = $orderProductEntity;
    }
}

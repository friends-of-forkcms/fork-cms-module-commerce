<?php

namespace Backend\Modules\Catalog\Domain\OrderProductOption\Command;

use Backend\Modules\Catalog\Domain\OrderProductOption\OrderProductOption;
use Backend\Modules\Catalog\Domain\OrderProductOption\OrderProductOptionDataTransferObject;

final class UpdateOrderProductOption extends OrderProductOptionDataTransferObject
{
    public function __construct(OrderProductOption $orderProductOption)
    {
        parent::__construct($orderProductOption);
    }

    public function setOrderProductOptionEntity(OrderProductOption $orderProductOptionEntity): void
    {
        $this->orderProductOptionEntity = $orderProductOptionEntity;
    }
}

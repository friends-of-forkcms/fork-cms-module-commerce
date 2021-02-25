<?php

namespace Backend\Modules\Commerce\Domain\OrderProductOption\Command;

use Backend\Modules\Commerce\Domain\OrderProductOption\OrderProductOption;
use Backend\Modules\Commerce\Domain\OrderProductOption\OrderProductOptionDataTransferObject;

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

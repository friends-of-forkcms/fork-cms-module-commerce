<?php

namespace Backend\Modules\Catalog\Domain\OrderAddress\Command;

use Backend\Modules\Catalog\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Catalog\Domain\OrderAddress\OrderAddressDataTransferObject;

final class UpdateOrderAddress extends OrderAddressDataTransferObject
{
    public function __construct(OrderAddress $orderAddress)
    {
        parent::__construct($orderAddress);
    }

    public function setOrderAddressEntity(OrderAddress $orderAddressEntity): void
    {
        $this->orderAddressEntity = $orderAddressEntity;
    }
}

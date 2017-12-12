<?php

namespace Backend\Modules\Catalog\Domain\OrderVat\Command;

use Backend\Modules\Catalog\Domain\OrderVat\OrderVat;
use Backend\Modules\Catalog\Domain\OrderVat\OrderVatDataTransferObject;

final class UpdateOrderVat extends OrderVatDataTransferObject
{
    public function __construct(OrderVat $orderVat)
    {
        parent::__construct($orderVat);
    }

    public function setOrderVatEntity(OrderVat $orderVatEntity): void
    {
        $this->orderVatEntity = $orderVatEntity;
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\OrderVat\Command;

use Backend\Modules\Commerce\Domain\OrderVat\OrderVat;
use Backend\Modules\Commerce\Domain\OrderVat\OrderVatDataTransferObject;

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

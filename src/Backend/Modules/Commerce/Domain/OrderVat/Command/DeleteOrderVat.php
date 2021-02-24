<?php

namespace Backend\Modules\Commerce\Domain\OrderVat\Command;

use Backend\Modules\Commerce\Domain\OrderVat\OrderVat;

final class DeleteOrderVat
{
    public OrderVat $orderVat;

    public function __construct(OrderVat $orderVat)
    {
        $this->orderVat = $orderVat;
    }
}

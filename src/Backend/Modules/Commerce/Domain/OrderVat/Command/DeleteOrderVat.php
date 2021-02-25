<?php

namespace Backend\Modules\Commerce\Domain\OrderVat\Command;

use Backend\Modules\Commerce\Domain\OrderVat\OrderVat;

final class DeleteOrderVat
{
    /** @var OrderVat */
    public $orderVat;

    public function __construct(OrderVat $orderVat)
    {
        $this->orderVat = $orderVat;
    }
}

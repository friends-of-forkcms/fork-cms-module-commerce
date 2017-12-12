<?php

namespace Backend\Modules\Catalog\Domain\OrderVat\Command;

use Backend\Modules\Catalog\Domain\OrderVat\OrderVat;

final class DeleteOrderVat
{
    /** @var OrderVat */
    public $orderVat;

    public function __construct(OrderVat $orderVat)
    {
        $this->orderVat = $orderVat;
    }
}

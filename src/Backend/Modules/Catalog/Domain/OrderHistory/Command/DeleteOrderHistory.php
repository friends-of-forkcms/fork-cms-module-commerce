<?php

namespace Backend\Modules\Catalog\Domain\OrderHistory\Command;

use Backend\Modules\Catalog\Domain\OrderHistory\OrderHistory;

final class DeleteOrderHistory
{
    /** @var OrderHistory */
    public $orderHistory;

    public function __construct(OrderHistory $orderHistory)
    {
        $this->vat = $orderHistory;
    }
}

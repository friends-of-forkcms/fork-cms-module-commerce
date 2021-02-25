<?php

namespace Backend\Modules\Commerce\Domain\OrderHistory\Command;

use Backend\Modules\Commerce\Domain\OrderHistory\OrderHistory;

final class DeleteOrderHistory
{
    /** @var OrderHistory */
    public $orderHistory;

    public function __construct(OrderHistory $orderHistory)
    {
        $this->vat = $orderHistory;
    }
}

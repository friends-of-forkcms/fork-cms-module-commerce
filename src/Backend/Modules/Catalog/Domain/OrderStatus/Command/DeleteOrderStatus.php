<?php

namespace Backend\Modules\Catalog\Domain\OrderStatus\Command;

use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatus;

final class DeleteOrderStatus
{
    /** @var OrderStatus */
    public $orderStatus;

    public function __construct(OrderStatus $orderStatus)
    {
        $this->vat = $orderStatus;
    }
}

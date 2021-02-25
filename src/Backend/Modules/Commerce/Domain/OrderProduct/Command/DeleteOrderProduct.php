<?php

namespace Backend\Modules\Commerce\Domain\OrderProduct\Command;

use Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct;

final class DeleteOrderProduct
{
    /** @var OrderProduct */
    public $orderProduct;

    public function __construct(OrderProduct $orderProduct)
    {
        $this->orderProduct = $orderProduct;
    }
}

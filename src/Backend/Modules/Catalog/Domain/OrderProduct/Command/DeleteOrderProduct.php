<?php

namespace Backend\Modules\Catalog\Domain\OrderProduct\Command;

use Backend\Modules\Catalog\Domain\OrderProduct\OrderProduct;

final class DeleteOrderProduct
{
    /** @var OrderProduct */
    public $orderProduct;

    public function __construct(OrderProduct $orderProduct)
    {
        $this->orderProduct = $orderProduct;
    }
}

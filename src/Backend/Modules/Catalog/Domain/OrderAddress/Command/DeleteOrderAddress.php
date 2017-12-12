<?php

namespace Backend\Modules\Catalog\Domain\OrderAddress\Command;

use Backend\Modules\Catalog\Domain\OrderAddress\OrderAddress;

final class DeleteOrderAddress
{
    /** @var OrderAddress */
    public $orderAddress;

    public function __construct(OrderAddress $orderAddress)
    {
        $this->orderAddress = $orderAddress;
    }
}

<?php

namespace Backend\Modules\Catalog\Domain\OrderProductOption\Command;

use Backend\Modules\Catalog\Domain\OrderProductOption\OrderProductOption;

final class DeleteOrderProductOption
{
    /** @var OrderProductOption */
    public $orderProductOption;

    public function __construct(OrderProductOption $orderProductOption)
    {
        $this->orderProductOption = $orderProductOption;
    }
}

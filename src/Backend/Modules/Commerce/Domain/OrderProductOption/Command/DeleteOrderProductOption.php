<?php

namespace Backend\Modules\Commerce\Domain\OrderProductOption\Command;

use Backend\Modules\Commerce\Domain\OrderProductOption\OrderProductOption;

final class DeleteOrderProductOption
{
    /** @var OrderProductOption */
    public $orderProductOption;

    public function __construct(OrderProductOption $orderProductOption)
    {
        $this->orderProductOption = $orderProductOption;
    }
}

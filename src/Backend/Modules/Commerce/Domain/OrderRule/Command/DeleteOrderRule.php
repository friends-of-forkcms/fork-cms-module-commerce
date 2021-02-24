<?php

namespace Backend\Modules\Commerce\Domain\OrderRule\Command;

use Backend\Modules\Commerce\Domain\OrderRule\OrderRule;

final class DeleteOrderRule
{
    public OrderRule $orderRule;

    public function __construct(OrderRule $orderRule)
    {
        $this->orderRule = $orderRule;
    }
}

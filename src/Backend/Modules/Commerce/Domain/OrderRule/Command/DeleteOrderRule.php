<?php

namespace Backend\Modules\Commerce\Domain\OrderRule\Command;

use Backend\Modules\Commerce\Domain\OrderRule\OrderRule;

final class DeleteOrderRule
{
    /** @var OrderRule */
    public $orderRule;

    public function __construct(OrderRule $orderRule)
    {
        $this->orderRule = $orderRule;
    }
}

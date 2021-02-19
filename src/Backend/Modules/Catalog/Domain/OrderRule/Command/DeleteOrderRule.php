<?php

namespace Backend\Modules\Catalog\Domain\OrderRule\Command;

use Backend\Modules\Catalog\Domain\OrderRule\OrderRule;

final class DeleteOrderRule
{
    /** @var OrderRule */
    public $orderRule;

    public function __construct(OrderRule $orderRule)
    {
        $this->orderRule = $orderRule;
    }
}

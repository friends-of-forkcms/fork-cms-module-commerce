<?php

namespace Backend\Modules\Commerce\Domain\CartRule\Command;

use Backend\Modules\Commerce\Domain\CartRule\CartRule;

final class DeleteCartRule
{
    /** @var CartRule */
    public $cartRule;

    public function __construct(CartRule $cartRule)
    {
        $this->cartRule = $cartRule;
    }
}

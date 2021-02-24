<?php

namespace Backend\Modules\Commerce\Domain\CartRule\Command;

use Backend\Modules\Commerce\Domain\CartRule\CartRule;

final class DeleteCartRule
{
    public CartRule $cartRule;

    public function __construct(CartRule $cartRule)
    {
        $this->cartRule = $cartRule;
    }
}

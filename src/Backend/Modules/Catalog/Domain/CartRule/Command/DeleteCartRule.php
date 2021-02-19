<?php

namespace Backend\Modules\Catalog\Domain\CartRule\Command;

use Backend\Modules\Catalog\Domain\CartRule\CartRule;

final class DeleteCartRule
{
    /** @var CartRule */
    public $cartRule;

    public function __construct(CartRule $cartRule)
    {
        $this->cartRule = $cartRule;
    }
}

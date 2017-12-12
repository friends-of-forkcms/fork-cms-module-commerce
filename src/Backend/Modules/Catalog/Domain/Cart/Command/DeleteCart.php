<?php

namespace Backend\Modules\Catalog\Domain\Cart\Command;

use Backend\Modules\Catalog\Domain\Cart\Cart;

final class DeleteCart
{
    /** @var Cart */
    public $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }
}

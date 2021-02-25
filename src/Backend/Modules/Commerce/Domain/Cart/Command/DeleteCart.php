<?php

namespace Backend\Modules\Commerce\Domain\Cart\Command;

use Backend\Modules\Commerce\Domain\Cart\Cart;

final class DeleteCart
{
    /** @var Cart */
    public $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }
}

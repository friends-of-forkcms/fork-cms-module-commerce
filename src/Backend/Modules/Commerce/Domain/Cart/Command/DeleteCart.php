<?php

namespace Backend\Modules\Commerce\Domain\Cart\Command;

use Backend\Modules\Commerce\Domain\Cart\Cart;

final class DeleteCart
{
    public Cart $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }
}

<?php

namespace Backend\Modules\Catalog\Domain\Cart\Event;

use Backend\Modules\Catalog\Domain\Cart\Cart;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    /** @var Cart */
    private $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }
}

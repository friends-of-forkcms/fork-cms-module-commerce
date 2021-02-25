<?php

namespace Backend\Modules\Commerce\Domain\Cart\Event;

use Backend\Modules\Commerce\Domain\Cart\Cart;
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

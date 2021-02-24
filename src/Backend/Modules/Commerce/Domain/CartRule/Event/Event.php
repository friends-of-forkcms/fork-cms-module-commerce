<?php

namespace Backend\Modules\Commerce\Domain\CartRule\Event;

use Backend\Modules\Commerce\Domain\CartRule\CartRule;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    private CartRule $cartRule;

    public function __construct(CartRule $cartRule)
    {
        $this->cartRule = $cartRule;
    }

    public function getCartRule(): CartRule
    {
        return $this->cartRule;
    }
}

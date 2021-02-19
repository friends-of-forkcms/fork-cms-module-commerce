<?php

namespace Backend\Modules\Catalog\Domain\CartRule\Event;

use Backend\Modules\Catalog\Domain\CartRule\CartRule;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    /** @var CartRule */
    private $cartRule;

    public function __construct(CartRule $cartRule)
    {
        $this->cartRule = $cartRule;
    }

    public function getCartRule(): CartRule
    {
        return $this->cartRule;
    }
}

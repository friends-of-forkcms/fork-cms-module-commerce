<?php

namespace Backend\Modules\Catalog\Domain\Quote\Event;

use Backend\Modules\Catalog\Domain\Cart\Cart;
use Backend\Modules\Catalog\Domain\Quote\QuoteDataTransferObject;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    /** @var QuoteDataTransferObject */
    private $quote;

    /** @var Cart */
    private $cart;

    public function __construct(QuoteDataTransferObject $quote, Cart $cart)
    {
        $this->quote = $quote;
        $this->cart = $cart;
    }

    public function getQuote(): QuoteDataTransferObject
    {
        return $this->quote;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }
}

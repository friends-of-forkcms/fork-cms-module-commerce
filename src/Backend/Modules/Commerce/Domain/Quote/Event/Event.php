<?php

namespace Backend\Modules\Commerce\Domain\Quote\Event;

use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\Quote\QuoteDataTransferObject;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    private QuoteDataTransferObject $quote;

    private Cart $cart;

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

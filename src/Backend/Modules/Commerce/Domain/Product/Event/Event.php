<?php

namespace Backend\Modules\Commerce\Domain\Product\Event;

use Backend\Modules\Commerce\Domain\Product\Product;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    private Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }
}

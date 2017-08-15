<?php

namespace Backend\Modules\Catalog\Domain\Product\Event;

use Backend\Modules\Catalog\Domain\Product\Product;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    /** @var Product */
    private $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }
}

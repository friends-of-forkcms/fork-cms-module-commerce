<?php

namespace Backend\Modules\Catalog\Domain\Product\Command;

use Backend\Modules\Catalog\Domain\Product\Product;

final class Delete
{
    /** @var Product */
    public $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\Product\Command;

use Backend\Modules\Commerce\Domain\Product\Product;

final class DeleteProduct
{
    public Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }
}

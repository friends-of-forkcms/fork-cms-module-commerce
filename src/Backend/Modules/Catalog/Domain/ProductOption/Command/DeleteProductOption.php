<?php

namespace Backend\Modules\Catalog\Domain\ProductOption\Command;

use Backend\Modules\Catalog\Domain\ProductOption\ProductOption;

final class DeleteProductOption
{
    /** @var ProductOption */
    public $productOption;

    public function __construct(ProductOption $productOption)
    {
        $this->productOption = $productOption;
    }
}

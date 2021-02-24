<?php

namespace Backend\Modules\Commerce\Domain\ProductOption\Command;

use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;

final class DeleteProductOption
{
    public ProductOption $productOption;

    public function __construct(ProductOption $productOption)
    {
        $this->productOption = $productOption;
    }
}

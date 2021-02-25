<?php

namespace Backend\Modules\Commerce\Domain\ProductOption\Command;

use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;

final class DeleteProductOption
{
    /** @var ProductOption */
    public $productOption;

    public function __construct(ProductOption $productOption)
    {
        $this->productOption = $productOption;
    }
}

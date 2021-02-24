<?php

namespace Backend\Modules\Commerce\Domain\ProductOptionValue\Command;

use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;

final class DeleteProductOptionValue
{
    public ProductOptionValue $specificationValue;

    public function __construct(ProductOptionValue $specificationValue)
    {
        $this->specificationValue = $specificationValue;
    }
}

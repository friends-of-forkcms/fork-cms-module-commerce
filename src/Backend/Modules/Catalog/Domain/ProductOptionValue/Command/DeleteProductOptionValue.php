<?php

namespace Backend\Modules\Catalog\Domain\ProductOptionValue\Command;

use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue;

final class DeleteProductOptionValue
{
    /** @var ProductOptionValue */
    public $specificationValue;

    public function __construct(ProductOptionValue $specificationValue)
    {
        $this->specificationValue = $specificationValue;
    }
}

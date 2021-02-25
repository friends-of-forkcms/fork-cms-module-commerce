<?php

namespace Backend\Modules\Commerce\Domain\ProductOptionValue\Command;

use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;

final class DeleteProductOptionValue
{
    /** @var ProductOptionValue */
    public $specificationValue;

    public function __construct(ProductOptionValue $specificationValue)
    {
        $this->specificationValue = $specificationValue;
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\ProductOptionValue\Command;

use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValueDataTransferObject;

final class CreateProductOptionValue extends ProductOptionValueDataTransferObject
{
    public function setProductOptionValueEntity(ProductOptionValue $productOptionValue): void
    {
        $this->productOptionValueEntity = $productOptionValue;
    }
}

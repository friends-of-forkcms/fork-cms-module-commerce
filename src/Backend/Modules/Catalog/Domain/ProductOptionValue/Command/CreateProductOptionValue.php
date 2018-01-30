<?php

namespace Backend\Modules\Catalog\Domain\ProductOptionValue\Command;

use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue;
use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValueDataTransferObject;

final class CreateProductOptionValue extends ProductOptionValueDataTransferObject
{
    public function setProductOptionValueEntity(ProductOptionValue $productOptionValue): void
    {
        $this->productOptionValueEntity = $productOptionValue;
    }
}

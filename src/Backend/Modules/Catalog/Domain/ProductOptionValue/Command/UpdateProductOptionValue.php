<?php

namespace Backend\Modules\Catalog\Domain\ProductOptionValue\Command;

use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue;
use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValueDataTransferObject;

final class UpdateProductOptionValue extends ProductOptionValueDataTransferObject
{
    public function setProductOptionValueEntity(ProductOptionValue $specificationValueEntity): void
    {
        $this->specificationValueEntity = $specificationValueEntity;
    }
}

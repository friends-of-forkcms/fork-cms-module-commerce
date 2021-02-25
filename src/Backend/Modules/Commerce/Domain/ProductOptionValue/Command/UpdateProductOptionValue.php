<?php

namespace Backend\Modules\Commerce\Domain\ProductOptionValue\Command;

use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValueDataTransferObject;

final class UpdateProductOptionValue extends ProductOptionValueDataTransferObject
{
    public function setProductOptionValueEntity(ProductOptionValue $specificationValueEntity): void
    {
        $this->specificationValueEntity = $specificationValueEntity;
    }
}

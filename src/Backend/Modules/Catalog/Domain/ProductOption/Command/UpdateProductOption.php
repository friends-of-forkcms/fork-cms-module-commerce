<?php

namespace Backend\Modules\Catalog\Domain\ProductOption\Command;

use Backend\Modules\Catalog\Domain\ProductOption\ProductOption;
use Backend\Modules\Catalog\Domain\ProductOption\ProductOptionDataTransferObject;

final class UpdateProductOption extends ProductOptionDataTransferObject
{
    public function setProductOptionEntity(ProductOption $productOptionEntity): void
    {
        $this->productOptionEntity = $productOptionEntity;
    }
}

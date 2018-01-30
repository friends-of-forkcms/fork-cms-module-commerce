<?php

namespace Backend\Modules\Catalog\Domain\ProductOption\Command;

use Backend\Modules\Catalog\Domain\ProductOption\ProductOption;
use Backend\Modules\Catalog\Domain\ProductOption\ProductOptionDataTransferObject;

final class CreateProductOption extends ProductOptionDataTransferObject
{
    public function setProductOptionEntity(ProductOption $productOption): void
    {
        $this->productOptionEntity = $productOption;
    }
}

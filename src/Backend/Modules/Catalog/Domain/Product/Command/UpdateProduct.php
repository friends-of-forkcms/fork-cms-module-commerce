<?php

namespace Backend\Modules\Catalog\Domain\Product\Command;

use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\Product\ProductDataTransferObject;

final class UpdateProduct extends ProductDataTransferObject
{
    public function setProductEntity(Product $productEntity): void
    {
        $this->productEntity = $productEntity;
    }
}

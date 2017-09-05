<?php

namespace Backend\Modules\Catalog\Domain\Product\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\Product\ProductDataTransferObject;

final class CreateProduct extends ProductDataTransferObject
{
    public function __construct(Locale $locale = null)
    {
        parent::__construct();

        if ($locale === null) {
            $locale = Locale::workingLocale();
        }

        $this->locale = $locale;
    }

    public function setProductEntity(Product $product): void
    {
        $this->productEntity = $product;
    }
}

<?php

namespace Backend\Modules\Catalog\Domain\ProductOptionValue\Command;

use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue;
use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValueRepository;

final class UpdateProductOptionValueHandler
{
    /** @var ProductOptionValueRepository */
    private $specificationValueRepository;

    public function __construct(ProductOptionValueRepository $specificationValueRepository)
    {
        $this->specificationValueRepository = $specificationValueRepository;
    }

    public function handle(UpdateProductOptionValue $updateProductOptionValue): void
    {
        $specificationValue = ProductOptionValue::fromDataTransferObject($updateProductOptionValue);
        $this->specificationValueRepository->add($specificationValue);

        $updateProductOptionValue->setProductOptionValueEntity($specificationValue);
    }
}

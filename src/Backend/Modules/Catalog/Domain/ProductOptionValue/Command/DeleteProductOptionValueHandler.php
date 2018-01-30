<?php

namespace Backend\Modules\Catalog\Domain\ProductOptionValue\Command;

use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValueRepository;

final class DeleteProductOptionValueHandler
{
    /** @var ProductOptionValueRepository */
    private $specificationValueRepository;

    public function __construct(ProductOptionValueRepository $specificationValueRepository)
    {
        $this->specificationValueRepository = $specificationValueRepository;
    }

    public function handle(DeleteProductOptionValue $deleteProductOptionValue): void
    {
        $this->specificationValueRepository->removeById(
            $deleteProductOptionValue->specificationValue->getId()
        );
    }
}

<?php

namespace Backend\Modules\Catalog\Domain\SpecificationValue\Command;

use Backend\Modules\Catalog\Domain\SpecificationValue\ProductOptionValueRepository;

final class DeleteSpecificationValueHandler
{
    /** @var ProductOptionValueRepository */
    private $specificationValueRepository;

    public function __construct(ProductOptionValueRepository $specificationValueRepository)
    {
        $this->specificationValueRepository = $specificationValueRepository;
    }

    public function handle(DeleteSpecificationValue $deleteSpecificationValue): void
    {
        $this->specificationValueRepository->removeById(
            $deleteSpecificationValue->specificationValue->getId()
        );
    }
}

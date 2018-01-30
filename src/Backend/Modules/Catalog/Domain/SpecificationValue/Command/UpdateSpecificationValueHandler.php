<?php

namespace Backend\Modules\Catalog\Domain\SpecificationValue\Command;

use Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValue;
use Backend\Modules\Catalog\Domain\SpecificationValue\ProductOptionValueRepository;

final class UpdateSpecificationValueHandler
{
    /** @var ProductOptionValueRepository */
    private $specificationValueRepository;

    public function __construct(ProductOptionValueRepository $specificationValueRepository)
    {
        $this->specificationValueRepository = $specificationValueRepository;
    }

    public function handle(UpdateSpecificationValue $updateSpecificationValue): void
    {
        $specificationValue = SpecificationValue::fromDataTransferObject($updateSpecificationValue);
        $this->specificationValueRepository->add($specificationValue);

        $updateSpecificationValue->setSpecificationValueEntity($specificationValue);
    }
}

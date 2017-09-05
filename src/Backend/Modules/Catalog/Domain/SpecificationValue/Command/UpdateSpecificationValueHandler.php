<?php

namespace Backend\Modules\Catalog\Domain\SpecificationValue\Command;

use Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValue;
use Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValueRepository;

final class UpdateSpecificationValueHandler
{
    /** @var SpecificationValueRepository */
    private $specificationValueRepository;

    public function __construct(SpecificationValueRepository $specificationValueRepository)
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

<?php

namespace Backend\Modules\Commerce\Domain\SpecificationValue\Command;

use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValue;
use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValueRepository;

final class UpdateSpecificationValueHandler
{
    private SpecificationValueRepository $specificationValueRepository;

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

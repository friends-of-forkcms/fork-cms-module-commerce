<?php

namespace Backend\Modules\Commerce\Domain\SpecificationValue\Command;

use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValue;
use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValueRepository;

final class CreateSpecificationValueHandler
{
    private SpecificationValueRepository $specificationValueRepository;

    public function __construct(SpecificationValueRepository $specificationValueRepository)
    {
        $this->specificationValueRepository = $specificationValueRepository;
    }

    public function handle(CreateSpecificationValue $createSpecificationValue): void
    {
        $specificationValue = SpecificationValue::fromDataTransferObject($createSpecificationValue);
        $this->specificationValueRepository->add($specificationValue);

        $createSpecificationValue->setSpecificationValueEntity($specificationValue);
    }
}

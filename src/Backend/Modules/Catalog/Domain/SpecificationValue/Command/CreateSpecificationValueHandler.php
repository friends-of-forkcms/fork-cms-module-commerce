<?php

namespace Backend\Modules\Catalog\Domain\SpecificationValue\Command;

use Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValue;
use Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValueRepository;

final class CreateSpecificationValueHandler
{
    /** @var SpecificationValueRepository */
    private $specificationValueRepository;

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

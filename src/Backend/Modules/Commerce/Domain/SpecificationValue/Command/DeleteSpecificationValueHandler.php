<?php

namespace Backend\Modules\Commerce\Domain\SpecificationValue\Command;

use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValueRepository;

final class DeleteSpecificationValueHandler
{
    private SpecificationValueRepository $specificationValueRepository;

    public function __construct(SpecificationValueRepository $specificationValueRepository)
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

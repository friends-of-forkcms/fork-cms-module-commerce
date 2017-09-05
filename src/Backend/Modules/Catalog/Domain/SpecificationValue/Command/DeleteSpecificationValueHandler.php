<?php

namespace Backend\Modules\Catalog\Domain\SpecificationValue\Command;

use Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValueRepository;

final class DeleteSpecificationValueHandler
{
    /** @var SpecificationValueRepository */
    private $specificationValueRepository;

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

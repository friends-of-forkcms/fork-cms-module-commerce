<?php

namespace Backend\Modules\Commerce\Domain\Specification\Command;

use Backend\Modules\Commerce\Domain\Specification\Specification;
use Backend\Modules\Commerce\Domain\Specification\SpecificationRepository;

final class UpdateSpecificationHandler
{
    private SpecificationRepository $specificationRepository;

    public function __construct(SpecificationRepository $specificationRepository)
    {
        $this->specificationRepository = $specificationRepository;
    }

    public function handle(UpdateSpecification $updateSpecification): void
    {
        $specification = Specification::fromDataTransferObject($updateSpecification);
        $this->specificationRepository->add($specification);

        $updateSpecification->setSpecificationEntity($specification);
    }
}

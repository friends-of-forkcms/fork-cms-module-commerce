<?php

namespace Backend\Modules\Commerce\Domain\Specification\Command;

use Backend\Modules\Commerce\Domain\Specification\Specification;
use Backend\Modules\Commerce\Domain\Specification\SpecificationRepository;

final class CreateSpecificationHandler
{
    private SpecificationRepository $specificationRepository;

    public function __construct(SpecificationRepository $specificationRepository)
    {
        $this->specificationRepository = $specificationRepository;
    }

    public function handle(CreateSpecification $createSpecification): void
    {
        $specification = Specification::fromDataTransferObject($createSpecification);
        $this->specificationRepository->add($specification);

        $createSpecification->setSpecificationEntity($specification);
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\Specification\Command;

use Backend\Modules\Commerce\Domain\Specification\SpecificationRepository;

final class DeleteSpecificationHandler
{
    private SpecificationRepository $specificationRepository;

    public function __construct(SpecificationRepository $specificationRepository)
    {
        $this->specificationRepository = $specificationRepository;
    }

    public function handle(DeleteSpecification $deleteSpecification): void
    {
        $this->specificationRepository->removeByIdAndLocale(
            $deleteSpecification->specification->getId(),
            $deleteSpecification->specification->getLocale()
        );
    }
}

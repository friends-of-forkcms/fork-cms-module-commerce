<?php

namespace Backend\Modules\Catalog\Domain\Specification\Command;

use Backend\Modules\Catalog\Domain\Specification\SpecificationRepository;

final class DeleteHandler
{
    /** @var SpecificationRepository */
    private $specificationRepository;

    public function __construct(SpecificationRepository $specificationRepository)
    {
        $this->specificationRepository = $specificationRepository;
    }

    public function handle(Delete $deleteSpecification): void
    {
        $this->specificationRepository->removeByIdAndLocale(
            $deleteSpecification->specification->getId(),
            $deleteSpecification->specification->getLocale()
        );
    }
}

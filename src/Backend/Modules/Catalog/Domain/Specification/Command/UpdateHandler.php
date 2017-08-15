<?php

namespace Backend\Modules\Catalog\Domain\Specification\Command;

use Backend\Modules\Catalog\Domain\Specification\Specification;
use Backend\Modules\Catalog\Domain\Specification\SpecificationRepository;

final class UpdateHandler
{
    /** @var SpecificationRepository */
    private $specificationRepository;

    public function __construct(SpecificationRepository $specificationRepository)
    {
        $this->specificationRepository = $specificationRepository;
    }

    public function handle(Update $updateSpecification): void
    {
        $specification = Specification::fromDataTransferObject($updateSpecification);
        $this->specificationRepository->add($specification);

        $updateSpecification->setSpecificationEntity($specification);
    }
}

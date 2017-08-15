<?php

namespace Backend\Modules\Catalog\Domain\Specification\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Catalog\Domain\Specification\Specification;
use Backend\Modules\Catalog\Domain\Specification\SpecificationRepository;
use Common\ModuleExtraType;

final class CreateHandler
{
    /** @var SpecificationRepository */
    private $specificationRepository;

    public function __construct(SpecificationRepository $specificationRepository)
    {
        $this->specificationRepository = $specificationRepository;
    }

    public function handle(Create $createSpecification): void
    {
        $createSpecification->sequence = $this->specificationRepository->getNextSequence(
            $createSpecification->locale
        );

        $specification = Specification::fromDataTransferObject($createSpecification);
        $this->specificationRepository->add($specification);

        $createSpecification->setSpecificationEntity($specification);
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\Specification\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Commerce\Domain\Specification\Specification;
use Backend\Modules\Commerce\Domain\Specification\SpecificationRepository;
use Common\ModuleExtraType;

final class CreateSpecificationHandler
{
    /** @var SpecificationRepository */
    private $specificationRepository;

    public function __construct(SpecificationRepository $specificationRepository)
    {
        $this->specificationRepository = $specificationRepository;
    }

    public function handle(CreateSpecification $createSpecification): void
    {
        $createSpecification->sequence = $this->specificationRepository->getNextSequence(
            $createSpecification->locale
        );

        $specification = Specification::fromDataTransferObject($createSpecification);
        $this->specificationRepository->add($specification);

        $createSpecification->setSpecificationEntity($specification);
    }
}

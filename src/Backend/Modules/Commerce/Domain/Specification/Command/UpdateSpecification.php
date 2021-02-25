<?php

namespace Backend\Modules\Commerce\Domain\Specification\Command;

use Backend\Modules\Commerce\Domain\Specification\Specification;
use Backend\Modules\Commerce\Domain\Specification\SpecificationDataTransferObject;

final class UpdateSpecification extends SpecificationDataTransferObject
{
    public function __construct(Specification $specification)
    {
        parent::__construct($specification);
    }

    public function setSpecificationEntity(Specification $specificationEntity): void
    {
        $this->specificationEntity = $specificationEntity;
    }
}

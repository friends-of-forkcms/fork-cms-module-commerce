<?php

namespace Backend\Modules\Catalog\Domain\Specification\Command;

use Backend\Modules\Catalog\Domain\Specification\Specification;
use Backend\Modules\Catalog\Domain\Specification\SpecificationDataTransferObject;

final class Update extends SpecificationDataTransferObject
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

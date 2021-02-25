<?php

namespace Backend\Modules\Commerce\Domain\SpecificationValue\Command;

use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValue;
use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValueDataTransferObject;

final class CreateSpecificationValue extends SpecificationValueDataTransferObject
{
    public function setSpecificationValueEntity(SpecificationValue $specificationValue): void
    {
        $this->specificationValueEntity = $specificationValue;
    }
}

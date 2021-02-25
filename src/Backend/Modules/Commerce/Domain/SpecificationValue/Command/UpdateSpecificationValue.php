<?php

namespace Backend\Modules\Commerce\Domain\SpecificationValue\Command;

use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValue;
use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValueDataTransferObject;

final class UpdateSpecificationValue extends SpecificationValueDataTransferObject
{
    public function setSpecificationValueEntity(SpecificationValue $specificationValueEntity): void
    {
        $this->specificationValueEntity = $specificationValueEntity;
    }
}

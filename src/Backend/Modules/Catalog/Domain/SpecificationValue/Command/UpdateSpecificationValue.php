<?php

namespace Backend\Modules\Catalog\Domain\SpecificationValue\Command;

use Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValue;
use Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValueDataTransferObject;

final class UpdateSpecificationValue extends SpecificationValueDataTransferObject
{
    public function setSpecificationValueEntity(SpecificationValue $specificationValueEntity): void
    {
        $this->specificationValueEntity = $specificationValueEntity;
    }
}

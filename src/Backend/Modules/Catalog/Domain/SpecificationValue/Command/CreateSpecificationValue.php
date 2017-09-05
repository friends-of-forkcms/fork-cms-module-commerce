<?php

namespace Backend\Modules\Catalog\Domain\SpecificationValue\Command;

use Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValue;
use Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValueDataTransferObject;

final class CreateSpecificationValue extends SpecificationValueDataTransferObject
{
    public function setSpecificationValueEntity(SpecificationValue $specificationValue): void
    {
        $this->specificationValueEntity = $specificationValue;
    }
}

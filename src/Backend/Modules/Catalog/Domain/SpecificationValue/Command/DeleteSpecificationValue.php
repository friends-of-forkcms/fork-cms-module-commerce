<?php

namespace Backend\Modules\Catalog\Domain\SpecificationValue\Command;

use Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValue;

final class DeleteSpecificationValue
{
    /** @var SpecificationValue */
    public $specificationValue;

    public function __construct(SpecificationValue $specificationValue)
    {
        $this->specificationValue = $specificationValue;
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\SpecificationValue\Command;

use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValue;

final class DeleteSpecificationValue
{
    /** @var SpecificationValue */
    public $specificationValue;

    public function __construct(SpecificationValue $specificationValue)
    {
        $this->specificationValue = $specificationValue;
    }
}

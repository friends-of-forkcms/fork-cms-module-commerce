<?php

namespace Backend\Modules\Commerce\Domain\Specification\Command;

use Backend\Modules\Commerce\Domain\Specification\Specification;

final class DeleteSpecification
{
    /** @var Specification */
    public $specification;

    public function __construct(Specification $specification)
    {
        $this->specification = $specification;
    }
}

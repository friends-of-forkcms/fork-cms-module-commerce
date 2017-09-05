<?php

namespace Backend\Modules\Catalog\Domain\Specification\Command;

use Backend\Modules\Catalog\Domain\Specification\Specification;

final class DeleteSpecification
{
    /** @var Specification */
    public $specification;

    public function __construct(Specification $specification)
    {
        $this->specification = $specification;
    }
}

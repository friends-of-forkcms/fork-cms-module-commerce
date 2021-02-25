<?php

namespace Backend\Modules\Commerce\Domain\Specification\Event;

use Backend\Modules\Commerce\Domain\Specification\Specification;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    /** @var Specification */
    private $specification;

    public function __construct(Specification $specification)
    {
        $this->specification = $specification;
    }

    public function getSpecification(): Specification
    {
        return $this->specification;
    }
}

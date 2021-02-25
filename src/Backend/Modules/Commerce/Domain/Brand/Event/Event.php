<?php

namespace Backend\Modules\Commerce\Domain\Brand\Event;

use Backend\Modules\Commerce\Domain\Brand\Brand;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    /** @var Brand */
    private $brand;

    public function __construct(Brand $brand)
    {
        $this->brand = $brand;
    }

    public function getBrand(): Brand
    {
        return $this->brand;
    }
}

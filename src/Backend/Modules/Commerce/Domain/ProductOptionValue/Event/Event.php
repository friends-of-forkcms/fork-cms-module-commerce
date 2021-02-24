<?php

namespace Backend\Modules\Commerce\Domain\ProductOptionValue\Event;

use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    private ProductOptionValue $specificationValue;

    public function __construct(ProductOptionValue $specificationValue)
    {
        $this->specificationValue = $specificationValue;
    }

    public function getProductOptionValue(): ProductOptionValue
    {
        return $this->specificationValue;
    }
}

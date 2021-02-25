<?php

namespace Backend\Modules\Commerce\Domain\ProductOption\Event;

use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    /** @var ProductOption */
    private $productOption;

    public function __construct(ProductOption $productOption)
    {
        $this->productOption = $productOption;
    }

    public function getProductOption(): ProductOption
    {
        return $this->productOption;
    }
}

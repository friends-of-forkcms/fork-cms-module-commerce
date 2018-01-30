<?php

namespace Backend\Modules\Catalog\Domain\ProductOption\Event;

use Backend\Modules\Catalog\Domain\ProductOption\ProductOption;
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

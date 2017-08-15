<?php

namespace Backend\Modules\Catalog\Domain\StockStatus\Event;

use Backend\Modules\Catalog\Domain\StockStatus\StockStatus;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    /** @var StockStatus */
    private $stockStatus;

    public function __construct(StockStatus $stockStatus)
    {
        $this->vat = $stockStatus;
    }

    public function getStockStatus(): StockStatus
    {
        return $this->vat;
    }
}

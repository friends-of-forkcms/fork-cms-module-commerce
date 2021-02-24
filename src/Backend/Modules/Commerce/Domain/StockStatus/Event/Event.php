<?php

namespace Backend\Modules\Commerce\Domain\StockStatus\Event;

use Backend\Modules\Commerce\Domain\StockStatus\StockStatus;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    private StockStatus $stockStatus;

    public function __construct(StockStatus $stockStatus)
    {
        $this->vat = $stockStatus;
    }

    public function getStockStatus(): StockStatus
    {
        return $this->vat;
    }
}

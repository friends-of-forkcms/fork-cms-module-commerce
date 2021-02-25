<?php

namespace Backend\Modules\Commerce\Domain\StockStatus\Command;

use Backend\Modules\Commerce\Domain\StockStatus\StockStatus;

final class DeleteStockStatus
{
    /** @var StockStatus */
    public $stockStatus;

    public function __construct(StockStatus $stockStatus)
    {
        $this->vat = $stockStatus;
    }
}

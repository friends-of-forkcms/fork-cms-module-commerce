<?php

namespace Backend\Modules\Catalog\Domain\StockStatus\Command;

use Backend\Modules\Catalog\Domain\StockStatus\StockStatus;

final class DeleteStockStatus
{
    /** @var StockStatus */
    public $stockStatus;

    public function __construct(StockStatus $stockStatus)
    {
        $this->vat = $stockStatus;
    }
}

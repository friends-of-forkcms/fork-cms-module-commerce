<?php

namespace Backend\Modules\Catalog\Domain\StockStatus\Command;

use Backend\Modules\Catalog\Domain\StockStatus\StockStatus;
use Backend\Modules\Catalog\Domain\StockStatus\StockStatusDataTransferObject;

final class Update extends StockStatusDataTransferObject
{
    public function __construct(StockStatus $stockStatus)
    {
        parent::__construct($stockStatus);
    }

    public function setStockStatusEntity(StockStatus $stockStatusEntity): void
    {
        $this->vatEntity = $stockStatusEntity;
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\StockStatus\Command;

use Backend\Modules\Commerce\Domain\StockStatus\StockStatus;
use Backend\Modules\Commerce\Domain\StockStatus\StockStatusDataTransferObject;

final class UpdateStockStatus extends StockStatusDataTransferObject
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

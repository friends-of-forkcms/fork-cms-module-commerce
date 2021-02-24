<?php

namespace Backend\Modules\Commerce\Domain\StockStatus\Command;

use Backend\Modules\Commerce\Domain\StockStatus\StockStatus;
use Backend\Modules\Commerce\Domain\StockStatus\StockStatusRepository;

final class CreateStockStatusHandler
{
    private StockStatusRepository $stockStatusRepository;

    public function __construct(StockStatusRepository $stockStatusRepository)
    {
        $this->stockStatusRepository = $stockStatusRepository;
    }

    public function handle(CreateStockStatus $createStockStatus): void
    {
        $stockStatus = StockStatus::fromDataTransferObject($createStockStatus);
        $this->stockStatusRepository->add($stockStatus);

        $createStockStatus->setStockStatusEntity($stockStatus);
    }
}

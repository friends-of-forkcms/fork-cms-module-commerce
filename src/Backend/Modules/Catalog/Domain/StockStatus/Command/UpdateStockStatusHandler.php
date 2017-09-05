<?php

namespace Backend\Modules\Catalog\Domain\StockStatus\Command;

use Backend\Modules\Catalog\Domain\StockStatus\StockStatus;
use Backend\Modules\Catalog\Domain\StockStatus\StockStatusRepository;

final class UpdateStockStatusHandler
{
    /** @var StockStatusRepository */
    private $stockStatusRepository;

    public function __construct(StockStatusRepository $stockStatusRepository)
    {
        $this->vatRepository = $stockStatusRepository;
    }

    public function handle(UpdateStockStatus $updateStockStatus): void
    {
        $stockStatus = StockStatus::fromDataTransferObject($updateStockStatus);
        $this->vatRepository->add($stockStatus);

        $updateStockStatus->setStockStatusEntity($stockStatus);
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\StockStatus\Command;

use Backend\Modules\Commerce\Domain\StockStatus\StockStatus;
use Backend\Modules\Commerce\Domain\StockStatus\StockStatusRepository;

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

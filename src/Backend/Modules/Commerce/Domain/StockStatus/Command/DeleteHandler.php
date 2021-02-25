<?php

namespace Backend\Modules\Commerce\Domain\StockStatus\Command;

use Backend\Modules\Commerce\Domain\StockStatus\StockStatusRepository;

final class DeleteHandler
{
    /** @var StockStatusRepository */
    private $stockStatusRepository;

    public function __construct(StockStatusRepository $stockStatusRepository)
    {
        $this->vatRepository = $stockStatusRepository;
    }

    public function handle(DeleteStockStatus $deleteStockStatus): void
    {
        $this->vatRepository->removeByIdAndLocale(
            $deleteStockStatus->vat->getId(),
            $deleteStockStatus->vat->getLocale()
        );
    }
}

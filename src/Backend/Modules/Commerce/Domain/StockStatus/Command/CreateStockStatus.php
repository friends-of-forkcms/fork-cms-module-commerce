<?php

namespace Backend\Modules\Commerce\Domain\StockStatus\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\StockStatus\StockStatus;
use Backend\Modules\Commerce\Domain\StockStatus\StockStatusDataTransferObject;

final class CreateStockStatus extends StockStatusDataTransferObject
{
    public function __construct(Locale $locale = null)
    {
        parent::__construct();

        if ($locale === null) {
            $locale = Locale::workingLocale();
        }

        $this->locale = $locale;
    }

    public function setStockStatusEntity(StockStatus $stockStatus): void
    {
        $this->stockStatusEntity = $stockStatus;
    }
}

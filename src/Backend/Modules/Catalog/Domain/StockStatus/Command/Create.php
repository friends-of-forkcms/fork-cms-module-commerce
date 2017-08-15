<?php

namespace Backend\Modules\Catalog\Domain\StockStatus\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\StockStatus\StockStatus;
use Backend\Modules\Catalog\Domain\StockStatus\StockStatusDataTransferObject;

final class Create extends StockStatusDataTransferObject
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

<?php

namespace Backend\Modules\Commerce\Domain\OrderHistory\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\OrderHistory\OrderHistory;
use Backend\Modules\Commerce\Domain\OrderHistory\OrderHistoryDataTransferObject;

final class CreateOrderHistory extends OrderHistoryDataTransferObject
{
    public function __construct(Locale $locale = null)
    {
        parent::__construct();

        if ($locale === null) {
            $locale = Locale::workingLocale();
        }

        $this->locale = $locale;
    }

    public function setOrderHistoryEntity(OrderHistory $orderHistory): void
    {
        $this->orderHistoryEntity = $orderHistory;
    }
}

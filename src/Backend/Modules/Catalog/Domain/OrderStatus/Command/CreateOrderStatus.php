<?php

namespace Backend\Modules\Catalog\Domain\OrderStatus\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatus;
use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatusDataTransferObject;

final class CreateOrderStatus extends OrderStatusDataTransferObject
{
    public function __construct(Locale $locale = null)
    {
        parent::__construct();

        if ($locale === null) {
            $locale = Locale::workingLocale();
        }

        $this->locale = $locale;
    }

    public function setOrderStatusEntity(OrderStatus $orderStatus): void
    {
        $this->orderStatusEntity = $orderStatus;
    }
}

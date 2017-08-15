<?php

namespace Backend\Modules\Catalog\Domain\Order\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\Order\OrderDataTransferObject;

final class Create extends OrderDataTransferObject
{
    public function __construct(Locale $locale = null)
    {
        parent::__construct();

        if ($locale === null) {
            $locale = Locale::workingLocale();
        }

        $this->locale = $locale;
    }

    public function setOrderEntity(Order $order): void
    {
        $this->orderEntity = $order;
    }
}

<?php

namespace Backend\Modules\Catalog\Domain\OrderProduct\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\OrderProduct\OrderProduct;
use Backend\Modules\Catalog\Domain\OrderProduct\OrderProductDataTransferObject;

final class CreateOrderProduct extends OrderProductDataTransferObject
{
    public function __construct(Locale $locale = null)
    {
        parent::__construct();

        if ($locale === null) {
            $locale = Locale::workingLocale();
        }

        $this->locale = $locale;
    }

    public function setOrderProductEntity(OrderProduct $orderProduct): void
    {
        $this->orderProductEntity = $orderProduct;
    }
}

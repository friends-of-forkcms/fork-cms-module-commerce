<?php

namespace Backend\Modules\Catalog\Domain\OrderAddress\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Catalog\Domain\OrderAddress\OrderAddressDataTransferObject;

final class CreateOrderAddress extends OrderAddressDataTransferObject
{
    public function __construct(Locale $locale = null)
    {
        parent::__construct();

        if ($locale === null) {
            $locale = Locale::workingLocale();
        }

        $this->locale = $locale;
    }

    public function setOrderAddressEntity(OrderAddress $orderAddress): void
    {
        $this->orderAddressEntity = $orderAddress;
    }
}

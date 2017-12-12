<?php

namespace Backend\Modules\Catalog\Domain\OrderVat\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\OrderVat\OrderVat;
use Backend\Modules\Catalog\Domain\OrderVat\OrderVatDataTransferObject;

final class CreateOrderVat extends OrderVatDataTransferObject
{
    public function __construct(Locale $locale = null)
    {
        parent::__construct();

        if ($locale === null) {
            $locale = Locale::workingLocale();
        }

        $this->locale = $locale;
    }

    public function setOrderVatEntity(OrderVat $orderVat): void
    {
        $this->orderVatEntity = $orderVat;
    }
}

<?php

namespace Backend\Modules\Catalog\Domain\OrderProductOption\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\OrderProductOption\OrderProductOption;
use Backend\Modules\Catalog\Domain\OrderProductOption\OrderProductOptionDataTransferObject;

final class CreateOrderProductOption extends OrderProductOptionDataTransferObject
{
    public function __construct(Locale $locale = null)
    {
        parent::__construct();

        if ($locale === null) {
            $locale = Locale::workingLocale();
        }

        $this->locale = $locale;
    }

    public function setOrderProductOptionEntity(OrderProductOption $orderProductOption): void
    {
        $this->orderProductOptionEntity = $orderProductOption;
    }
}

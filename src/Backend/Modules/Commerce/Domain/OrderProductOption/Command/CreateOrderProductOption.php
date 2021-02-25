<?php

namespace Backend\Modules\Commerce\Domain\OrderProductOption\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\OrderProductOption\OrderProductOption;
use Backend\Modules\Commerce\Domain\OrderProductOption\OrderProductOptionDataTransferObject;

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

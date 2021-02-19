<?php

namespace Backend\Modules\Catalog\Domain\OrderProductNotification\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\OrderProductNotification\OrderProductNotification;
use Backend\Modules\Catalog\Domain\OrderProductNotification\OrderProductNotificationDataTransferObject;

final class CreateOrderProductNotification extends OrderProductNotificationDataTransferObject
{
    public function __construct(Locale $locale = null)
    {
        parent::__construct();

        if ($locale === null) {
            $locale = Locale::workingLocale();
        }

        $this->locale = $locale;
    }

    public function setOrderProductNotificationEntity(OrderProductNotification $orderProductNotification): void
    {
        $this->orderProductNotificationEntity = $orderProductNotification;
    }
}

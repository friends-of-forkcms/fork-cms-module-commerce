<?php

namespace Backend\Modules\Commerce\Domain\OrderProductNotification\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\OrderProductNotification\OrderProductNotification;
use Backend\Modules\Commerce\Domain\OrderProductNotification\OrderProductNotificationDataTransferObject;

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

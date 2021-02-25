<?php

namespace Backend\Modules\Commerce\Domain\OrderProductNotification\Command;

use Backend\Modules\Commerce\Domain\OrderProductNotification\OrderProductNotification;
use Backend\Modules\Commerce\Domain\OrderProductNotification\OrderProductNotificationDataTransferObject;

final class UpdateOrderProductNotification extends OrderProductNotificationDataTransferObject
{
    public function __construct(OrderProductNotification $orderProductNotification)
    {
        parent::__construct($orderProductNotification);
    }

    public function setOrderProductNotificationEntity(OrderProductNotification $orderProductNotificationEntity): void
    {
        $this->orderProductNotificationEntity = $orderProductNotificationEntity;
    }
}

<?php

namespace Backend\Modules\Catalog\Domain\OrderProductNotification\Command;

use Backend\Modules\Catalog\Domain\OrderProductNotification\OrderProductNotification;
use Backend\Modules\Catalog\Domain\OrderProductNotification\OrderProductNotificationDataTransferObject;

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

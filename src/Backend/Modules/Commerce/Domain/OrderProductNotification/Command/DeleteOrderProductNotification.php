<?php

namespace Backend\Modules\Commerce\Domain\OrderProductNotification\Command;

use Backend\Modules\Commerce\Domain\OrderProductNotification\OrderProductNotification;

final class DeleteOrderProductNotification
{
    public OrderProductNotification $orderProductNotification;

    public function __construct(OrderProductNotification $orderProductNotification)
    {
        $this->orderProductNotification = $orderProductNotification;
    }
}

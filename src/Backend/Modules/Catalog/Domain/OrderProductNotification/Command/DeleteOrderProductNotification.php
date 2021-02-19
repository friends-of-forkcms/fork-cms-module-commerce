<?php

namespace Backend\Modules\Catalog\Domain\OrderProductNotification\Command;

use Backend\Modules\Catalog\Domain\OrderProductNotification\OrderProductNotification;

final class DeleteOrderProductNotification
{
    /** @var OrderProductNotification */
    public $orderProductNotification;

    public function __construct(OrderProductNotification $orderProductNotification)
    {
        $this->orderProductNotification = $orderProductNotification;
    }
}

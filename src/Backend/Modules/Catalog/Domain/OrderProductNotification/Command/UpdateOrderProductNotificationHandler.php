<?php

namespace Backend\Modules\Catalog\Domain\OrderProductNotification\Command;

use Backend\Modules\Catalog\Domain\OrderProductNotification\OrderProductNotification;
use Backend\Modules\Catalog\Domain\OrderProductNotification\OrderProductNotificationRepository;

final class UpdateOrderProductNotificationHandler
{
    /** @var OrderProductNotificationRepository */
    private $orderProductNotificationRepository;

    public function __construct(OrderProductNotificationRepository $orderProductNotificationRepository)
    {
        $this->orderProductNotificationRepository = $orderProductNotificationRepository;
    }

    public function handle(UpdateOrderProductNotification $updateOrderProductNotification): void
    {
        $orderProductNotification = OrderProductNotification::fromDataTransferObject($updateOrderProductNotification);
        $this->orderProductNotificationRepository->add($orderProductNotification);

        $updateOrderProductNotification->setOrderProductNotificationEntity($orderProductNotification);
    }
}

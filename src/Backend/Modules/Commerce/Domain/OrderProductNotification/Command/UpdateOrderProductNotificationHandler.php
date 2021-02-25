<?php

namespace Backend\Modules\Commerce\Domain\OrderProductNotification\Command;

use Backend\Modules\Commerce\Domain\OrderProductNotification\OrderProductNotification;
use Backend\Modules\Commerce\Domain\OrderProductNotification\OrderProductNotificationRepository;

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

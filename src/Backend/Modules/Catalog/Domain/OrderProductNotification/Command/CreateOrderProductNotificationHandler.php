<?php

namespace Backend\Modules\Catalog\Domain\OrderProductNotification\Command;

use Backend\Modules\Catalog\Domain\OrderProductNotification\OrderProductNotification;
use Backend\Modules\Catalog\Domain\OrderProductNotification\OrderProductNotificationRepository;

final class CreateOrderProductNotificationHandler
{
    /** @var OrderProductNotificationRepository */
    private $orderProductNotificationRepository;

    public function __construct(OrderProductNotificationRepository $orderProductNotificationRepository)
    {
        $this->orderProductNotificationRepository = $orderProductNotificationRepository;
    }

    public function handle(CreateOrderProductNotification $createOrderProductNotification): void
    {
        $orderProductNotification = OrderProductNotification::fromDataTransferObject($createOrderProductNotification);
        $this->orderProductNotificationRepository->add($orderProductNotification);

        $createOrderProductNotification->setOrderProductNotificationEntity($orderProductNotification);
    }
}

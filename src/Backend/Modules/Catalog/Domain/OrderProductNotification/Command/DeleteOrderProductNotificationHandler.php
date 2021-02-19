<?php

namespace Backend\Modules\Catalog\Domain\OrderProductNotification\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Catalog\Domain\OrderProductNotification\OrderProductNotificationRepository;

final class DeleteOrderProductNotificationHandler
{
    /** @var OrderProductNotificationRepository */
    private $orderProductNotificationRepository;

    public function __construct(OrderProductNotificationRepository $orderProductNotificationRepository)
    {
        $this->orderProductNotificationRepository = $orderProductNotificationRepository;
    }

    public function handle(DeleteOrderProductNotification $deleteOrderProductNotification): void
    {
        $this->orderProductNotificationRepository->removeByIdAndLocale(
            $deleteOrderProductNotification->orderProductNotification->getId(),
            $deleteOrderProductNotification->orderProductNotification->getLocale()
        );

        Model::deleteExtraById($deleteOrderProductNotification->orderProductNotification->getExtraId());
    }
}

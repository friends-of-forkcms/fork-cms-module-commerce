<?php

namespace Backend\Modules\Catalog\Domain\OrderStatus\Command;

use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatus;
use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatusRepository;

final class UpdateOrderStatusHandler
{
    /** @var OrderStatusRepository */
    private $orderStatusRepository;

    public function __construct(OrderStatusRepository $orderStatusRepository)
    {
        $this->vatRepository = $orderStatusRepository;
    }

    public function handle(UpdateOrderStatus $updateOrderStatus): void
    {
        $orderStatus = OrderStatus::fromDataTransferObject($updateOrderStatus);
        $this->vatRepository->add($orderStatus);

        $updateOrderStatus->setOrderStatusEntity($orderStatus);
    }
}

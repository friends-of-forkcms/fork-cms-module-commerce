<?php

namespace Backend\Modules\Commerce\Domain\OrderStatus\Command;

use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatusRepository;

final class CreateOrderStatusHandler
{
    private OrderStatusRepository $orderStatusRepository;

    public function __construct(OrderStatusRepository $orderStatusRepository)
    {
        $this->orderStatusRepository = $orderStatusRepository;
    }

    public function handle(CreateOrderStatus $createOrderStatus): void
    {
        $orderStatus = OrderStatus::fromDataTransferObject($createOrderStatus);
        $this->orderStatusRepository->add($orderStatus);

        $createOrderStatus->setOrderStatusEntity($orderStatus);
    }
}

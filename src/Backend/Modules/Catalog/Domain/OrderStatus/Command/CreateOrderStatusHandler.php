<?php

namespace Backend\Modules\Catalog\Domain\OrderStatus\Command;

use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatus;
use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatusRepository;

final class CreateOrderStatusHandler
{
    /** @var OrderStatusRepository */
    private $orderStatusRepository;

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

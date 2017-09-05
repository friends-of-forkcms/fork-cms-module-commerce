<?php

namespace Backend\Modules\Catalog\Domain\Order\Command;

use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\Order\OrderRepository;

final class UpdateOrderHandler
{
    /** @var OrderRepository */
    private $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function handle(UpdateOrder $updateOrder): void
    {
        $order = Order::fromDataTransferObject($updateOrder);
        $this->orderRepository->add($order);

        $updateOrder->setOrderEntity($order);
    }
}

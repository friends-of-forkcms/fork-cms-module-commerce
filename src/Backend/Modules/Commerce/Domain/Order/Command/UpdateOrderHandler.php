<?php

namespace Backend\Modules\Commerce\Domain\Order\Command;

use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\Order\OrderRepository;

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

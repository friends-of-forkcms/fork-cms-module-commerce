<?php

namespace Backend\Modules\Commerce\Domain\Order\Command;

use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\Order\OrderRepository;

final class CreateOrderHandler
{
    /** @var OrderRepository */
    private $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function handle(CreateOrder $createOrder): void
    {
        $order = Order::fromDataTransferObject($createOrder);
        $this->orderRepository->add($order);

        $createOrder->setOrderEntity($order);
    }
}

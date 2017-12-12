<?php

namespace Backend\Modules\Catalog\Domain\Order\Command;

use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\Order\OrderRepository;

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

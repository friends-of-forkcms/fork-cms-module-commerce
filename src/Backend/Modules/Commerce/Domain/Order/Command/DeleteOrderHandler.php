<?php

namespace Backend\Modules\Commerce\Domain\Order\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Commerce\Domain\Order\OrderRepository;

final class DeleteOrderHandler
{
    private OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function handle(DeleteCart $deleteOrder): void
    {
        $this->orderRepository->removeByIdAndLocale(
            $deleteOrder->order->getId(),
            $deleteOrder->order->getLocale()
        );

        Model::deleteExtraById($deleteOrder->order->getExtraId());
    }
}

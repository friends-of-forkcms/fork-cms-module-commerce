<?php

namespace Backend\Modules\Catalog\Domain\Order\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Catalog\Domain\Order\OrderRepository;

final class DeleteOrderHandler
{
    /** @var OrderRepository */
    private $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function handle(DeleteOrder $deleteOrder): void
    {
        $this->orderRepository->removeByIdAndLocale(
            $deleteOrder->order->getId(),
            $deleteOrder->order->getLocale()
        );

        Model::deleteExtraById($deleteOrder->order->getExtraId());
    }
}

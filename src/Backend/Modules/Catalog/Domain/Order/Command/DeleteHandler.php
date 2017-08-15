<?php

namespace Backend\Modules\Catalog\Domain\Order\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Catalog\Domain\Order\OrderRepository;

final class DeleteHandler
{
    /** @var OrderRepository */
    private $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function handle(Delete $deleteOrder): void
    {
        $this->orderRepository->removeByIdAndLocale(
            $deleteOrder->order->getId(),
            $deleteOrder->order->getLocale()
        );

        Model::deleteExtraById($deleteOrder->order->getExtraId());
    }
}

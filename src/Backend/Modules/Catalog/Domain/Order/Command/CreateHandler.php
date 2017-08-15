<?php

namespace Backend\Modules\Catalog\Domain\Order\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\Order\OrderRepository;
use Common\ModuleExtraType;

final class CreateHandler
{
    /** @var OrderRepository */
    private $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function handle(Create $createOrder): void
    {
        $createOrder->extraId = $this->getNewExtraId();
        $createOrder->sequence = $this->orderRepository->getNextSequence(
            $createOrder->locale,
            $createOrder->parent
        );

        $order = Order::fromDataTransferObject($createOrder);
        $this->orderRepository->add($order);

        $createOrder->setOrderEntity($order);
    }

    private function getNewExtraId(): int
    {
        return Model::insertExtra(
            ModuleExtraType::widget(),
            'Catalog',
            'Order'
        );
    }
}

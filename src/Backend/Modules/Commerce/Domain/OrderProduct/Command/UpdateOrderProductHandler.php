<?php

namespace Backend\Modules\Commerce\Domain\OrderProduct\Command;

use Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct;
use Backend\Modules\Commerce\Domain\OrderProduct\OrderProductRepository;

final class UpdateOrderProductHandler
{
    /** @var OrderProductRepository */
    private $orderProductRepository;

    public function __construct(OrderProductRepository $orderProductRepository)
    {
        $this->orderProductRepository = $orderProductRepository;
    }

    public function handle(UpdateOrderProduct $updateOrderProduct): void
    {
        $orderProduct = OrderProduct::fromDataTransferObject($updateOrderProduct);
        $this->orderProductRepository->add($orderProduct);

        $updateOrderProduct->setOrderProductEntity($orderProduct);
    }
}

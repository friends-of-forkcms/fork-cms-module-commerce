<?php

namespace Backend\Modules\Catalog\Domain\OrderProduct\Command;

use Backend\Modules\Catalog\Domain\OrderProduct\OrderProduct;
use Backend\Modules\Catalog\Domain\OrderProduct\OrderProductRepository;

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

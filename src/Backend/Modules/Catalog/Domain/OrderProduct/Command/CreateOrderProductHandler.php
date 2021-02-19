<?php

namespace Backend\Modules\Catalog\Domain\OrderProduct\Command;

use Backend\Modules\Catalog\Domain\OrderProduct\OrderProduct;
use Backend\Modules\Catalog\Domain\OrderProduct\OrderProductRepository;

final class CreateOrderProductHandler
{
    /** @var OrderProductRepository */
    private $orderProductRepository;

    public function __construct(OrderProductRepository $orderProductRepository)
    {
        $this->orderProductRepository = $orderProductRepository;
    }

    public function handle(CreateOrderProduct $createOrderProduct): void
    {
        $orderProduct = OrderProduct::fromDataTransferObject($createOrderProduct);

        // set the new product entity
        foreach ($createOrderProduct->productOptions as $productOption) {
            $productOption->setOrderProduct($orderProduct);
        }

        foreach ($createOrderProduct->productNotifications as $productNotification) {
            $productNotification->setOrderProduct($orderProduct);
        }

        $this->orderProductRepository->add($orderProduct);

        $createOrderProduct->setOrderProductEntity($orderProduct);
    }
}

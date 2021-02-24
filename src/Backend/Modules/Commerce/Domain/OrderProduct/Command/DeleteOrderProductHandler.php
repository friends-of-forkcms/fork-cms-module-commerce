<?php

namespace Backend\Modules\Commerce\Domain\OrderProduct\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Commerce\Domain\OrderProduct\OrderProductRepository;

final class DeleteOrderProductHandler
{
    private OrderProductRepository $orderProductRepository;

    public function __construct(OrderProductRepository $orderProductRepository)
    {
        $this->orderProductRepository = $orderProductRepository;
    }

    public function handle(DeleteOrderProduct $deleteOrderProduct): void
    {
        $this->orderProductRepository->removeByIdAndLocale(
            $deleteOrderProduct->orderProduct->getId(),
            $deleteOrderProduct->orderProduct->getLocale()
        );

        Model::deleteExtraById($deleteOrderProduct->orderProduct->getExtraId());
    }
}

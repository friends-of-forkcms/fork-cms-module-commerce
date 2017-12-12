<?php

namespace Backend\Modules\Catalog\Domain\OrderProduct\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Catalog\Domain\OrderProduct\OrderProductRepository;

final class DeleteOrderProductHandler
{
    /** @var OrderProductRepository */
    private $orderProductRepository;

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

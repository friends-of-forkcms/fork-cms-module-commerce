<?php

namespace Backend\Modules\Commerce\Domain\OrderProductOption\Command;

use Backend\Modules\Commerce\Domain\OrderProductOption\OrderProductOption;
use Backend\Modules\Commerce\Domain\OrderProductOption\OrderProductOptionRepository;

final class CreateOrderProductOptionHandler
{
    private OrderProductOptionRepository $orderProductOptionRepository;

    public function __construct(OrderProductOptionRepository $orderProductOptionRepository)
    {
        $this->orderProductOptionRepository = $orderProductOptionRepository;
    }

    public function handle(CreateOrderProductOption $createOrderProductOption): void
    {
        $orderProductOption = OrderProductOption::fromDataTransferObject($createOrderProductOption);
        $this->orderProductOptionRepository->add($orderProductOption);

        $createOrderProductOption->setOrderProductOptionEntity($orderProductOption);
    }
}

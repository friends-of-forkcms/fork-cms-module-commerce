<?php

namespace Backend\Modules\Catalog\Domain\OrderProductOption\Command;

use Backend\Modules\Catalog\Domain\OrderProductOption\OrderProductOption;
use Backend\Modules\Catalog\Domain\OrderProductOption\OrderProductOptionRepository;

final class CreateOrderProductOptionHandler
{
    /** @var OrderProductOptionRepository */
    private $orderProductOptionRepository;

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

<?php

namespace Backend\Modules\Catalog\Domain\OrderProductOption\Command;

use Backend\Modules\Catalog\Domain\OrderProductOption\OrderProductOption;
use Backend\Modules\Catalog\Domain\OrderProductOption\OrderProductOptionRepository;

final class UpdateOrderProductOptionHandler
{
    /** @var OrderProductOptionRepository */
    private $orderProductOptionRepository;

    public function __construct(OrderProductOptionRepository $orderProductOptionRepository)
    {
        $this->orderProductOptionRepository = $orderProductOptionRepository;
    }

    public function handle(UpdateOrderProductOption $updateOrderProductOption): void
    {
        $orderProductOption = OrderProductOption::fromDataTransferObject($updateOrderProductOption);
        $this->orderProductOptionRepository->add($orderProductOption);

        $updateOrderProductOption->setOrderProductOptionEntity($orderProductOption);
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\OrderProductOption\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Commerce\Domain\OrderProductOption\OrderProductOptionRepository;

final class DeleteOrderProductOptionHandler
{
    private OrderProductOptionRepository $orderProductOptionRepository;

    public function __construct(OrderProductOptionRepository $orderProductOptionRepository)
    {
        $this->orderProductOptionRepository = $orderProductOptionRepository;
    }

    public function handle(DeleteOrderProductOption $deleteOrderProductOption): void
    {
        $this->orderProductOptionRepository->removeByIdAndLocale(
            $deleteOrderProductOption->orderProductOption->getId(),
            $deleteOrderProductOption->orderProductOption->getLocale()
        );

        Model::deleteExtraById($deleteOrderProductOption->orderProductOption->getExtraId());
    }
}

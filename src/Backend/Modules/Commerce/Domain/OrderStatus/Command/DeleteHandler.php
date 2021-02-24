<?php

namespace Backend\Modules\Commerce\Domain\OrderStatus\Command;

use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatusRepository;

final class DeleteHandler
{
    private OrderStatusRepository $orderStatusRepository;

    public function __construct(OrderStatusRepository $orderStatusRepository)
    {
        $this->vatRepository = $orderStatusRepository;
    }

    public function handle(DeleteOrderStatus $deleteOrderStatus): void
    {
        $this->vatRepository->removeByIdAndLocale(
            $deleteOrderStatus->vat->getId(),
            $deleteOrderStatus->vat->getLocale()
        );
    }
}

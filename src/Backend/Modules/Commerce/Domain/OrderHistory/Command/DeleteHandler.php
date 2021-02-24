<?php

namespace Backend\Modules\Commerce\Domain\OrderHistory\Command;

use Backend\Modules\Commerce\Domain\OrderHistory\OrderHistoryRepository;

final class DeleteHandler
{
    private OrderHistoryRepository $orderHistoryRepository;

    public function __construct(OrderHistoryRepository $orderHistoryRepository)
    {
        $this->vatRepository = $orderHistoryRepository;
    }

    public function handle(DeleteOrderHistory $deleteOrderHistory): void
    {
        $this->vatRepository->removeByIdAndLocale(
            $deleteOrderHistory->vat->getId(),
            $deleteOrderHistory->vat->getLocale()
        );
    }
}

<?php

namespace Backend\Modules\Catalog\Domain\OrderHistory\Command;

use Backend\Modules\Catalog\Domain\OrderHistory\OrderHistoryRepository;

final class DeleteHandler
{
    /** @var OrderHistoryRepository */
    private $orderHistoryRepository;

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

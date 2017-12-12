<?php

namespace Backend\Modules\Catalog\Domain\OrderHistory\Command;

use Backend\Modules\Catalog\Domain\OrderHistory\OrderHistory;
use Backend\Modules\Catalog\Domain\OrderHistory\OrderHistoryRepository;

final class CreateOrderHistoryHandler
{
    /** @var OrderHistoryRepository */
    private $orderHistoryRepository;

    public function __construct(OrderHistoryRepository $orderHistoryRepository)
    {
        $this->orderHistoryRepository = $orderHistoryRepository;
    }

    public function handle(CreateOrderHistory $createOrderHistory): void
    {
        $orderHistory = OrderHistory::fromDataTransferObject($createOrderHistory);
        $this->orderHistoryRepository->add($orderHistory);

        $createOrderHistory->setOrderHistoryEntity($orderHistory);
    }
}

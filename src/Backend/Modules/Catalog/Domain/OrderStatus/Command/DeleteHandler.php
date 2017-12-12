<?php

namespace Backend\Modules\Catalog\Domain\OrderStatus\Command;

use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatusRepository;

final class DeleteHandler
{
    /** @var OrderStatusRepository */
    private $orderStatusRepository;

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

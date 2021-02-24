<?php

namespace Backend\Modules\Commerce\Domain\OrderVat\Command;

use Backend\Modules\Commerce\Domain\OrderVat\OrderVat;
use Backend\Modules\Commerce\Domain\OrderVat\OrderVatRepository;

final class CreateOrderVatHandler
{
    private OrderVatRepository $orderVatRepository;

    public function __construct(OrderVatRepository $orderVatRepository)
    {
        $this->orderVatRepository = $orderVatRepository;
    }

    public function handle(CreateOrderVat $createOrderVat): void
    {
        $orderVat = OrderVat::fromDataTransferObject($createOrderVat);
        $this->orderVatRepository->add($orderVat);

        $createOrderVat->setOrderVatEntity($orderVat);
    }
}

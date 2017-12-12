<?php

namespace Backend\Modules\Catalog\Domain\OrderVat\Command;

use Backend\Modules\Catalog\Domain\OrderVat\OrderVat;
use Backend\Modules\Catalog\Domain\OrderVat\OrderVatRepository;

final class CreateOrderVatHandler
{
    /** @var OrderVatRepository */
    private $orderVatRepository;

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

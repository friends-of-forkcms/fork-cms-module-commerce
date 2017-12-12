<?php

namespace Backend\Modules\Catalog\Domain\OrderVat\Command;

use Backend\Modules\Catalog\Domain\OrderVat\OrderVat;
use Backend\Modules\Catalog\Domain\OrderVat\OrderVatRepository;

final class UpdateOrderVatHandler
{
    /** @var OrderVatRepository */
    private $orderVatRepository;

    public function __construct(OrderVatRepository $orderVatRepository)
    {
        $this->orderVatRepository = $orderVatRepository;
    }

    public function handle(UpdateOrderVat $updateOrderVat): void
    {
        $orderVat = OrderVat::fromDataTransferObject($updateOrderVat);
        $this->orderVatRepository->add($orderVat);

        $updateOrderVat->setOrderVatEntity($orderVat);
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\OrderVat\Command;

use Backend\Modules\Commerce\Domain\OrderVat\OrderVat;
use Backend\Modules\Commerce\Domain\OrderVat\OrderVatRepository;

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

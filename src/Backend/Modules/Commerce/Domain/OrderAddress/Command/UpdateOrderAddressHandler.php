<?php

namespace Backend\Modules\Commerce\Domain\OrderAddress\Command;

use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddressRepository;

final class UpdateOrderAddressHandler
{
    private OrderAddressRepository $orderAddressRepository;

    public function __construct(OrderAddressRepository $orderAddressRepository)
    {
        $this->orderAddressRepository = $orderAddressRepository;
    }

    public function handle(UpdateOrderAddress $updateOrderAddress): void
    {
        $orderAddress = OrderAddress::fromDataTransferObject($updateOrderAddress);
        $this->orderAddressRepository->add($orderAddress);

        $updateOrderAddress->setOrderAddressEntity($orderAddress);
    }
}

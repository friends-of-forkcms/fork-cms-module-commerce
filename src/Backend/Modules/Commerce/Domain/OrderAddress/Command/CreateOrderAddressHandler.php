<?php

namespace Backend\Modules\Commerce\Domain\OrderAddress\Command;

use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddressRepository;

final class CreateOrderAddressHandler
{
    private OrderAddressRepository $orderAddressRepository;

    public function __construct(OrderAddressRepository $orderAddressRepository)
    {
        $this->orderAddressRepository = $orderAddressRepository;
    }

    public function handle(CreateOrderAddress $createOrderAddress): void
    {
        $orderAddress = OrderAddress::fromDataTransferObject($createOrderAddress);
        $this->orderAddressRepository->add($orderAddress);

        $createOrderAddress->setOrderAddressEntity($orderAddress);
    }
}

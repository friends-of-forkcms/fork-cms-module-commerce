<?php

namespace Backend\Modules\Catalog\Domain\OrderAddress\Command;

use Backend\Modules\Catalog\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Catalog\Domain\OrderAddress\QuoteAddressRepository;

final class CreateOrderAddressHandler
{
    /** @var QuoteAddressRepository */
    private $orderAddressRepository;

    public function __construct(QuoteAddressRepository $orderAddressRepository)
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

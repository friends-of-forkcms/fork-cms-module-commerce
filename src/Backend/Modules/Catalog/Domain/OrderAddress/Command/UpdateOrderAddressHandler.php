<?php

namespace Backend\Modules\Catalog\Domain\OrderAddress\Command;

use Backend\Modules\Catalog\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Catalog\Domain\OrderAddress\QuoteAddressRepository;

final class UpdateOrderAddressHandler
{
    /** @var QuoteAddressRepository */
    private $orderAddressRepository;

    public function __construct(QuoteAddressRepository $orderAddressRepository)
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

<?php

namespace Backend\Modules\Catalog\Domain\OrderAddress\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Catalog\Domain\OrderAddress\QuoteAddressRepository;

final class DeleteOrderAddressHandler
{
    /** @var QuoteAddressRepository */
    private $orderAddressRepository;

    public function __construct(QuoteAddressRepository $orderAddressRepository)
    {
        $this->orderAddressRepository = $orderAddressRepository;
    }

    public function handle(DeleteOrderAddress $deleteOrderAddress): void
    {
        $this->orderAddressRepository->removeByIdAndLocale(
            $deleteOrderAddress->orderAddress->getId(),
            $deleteOrderAddress->orderAddress->getLocale()
        );

        Model::deleteExtraById($deleteOrderAddress->orderAddress->getExtraId());
    }
}

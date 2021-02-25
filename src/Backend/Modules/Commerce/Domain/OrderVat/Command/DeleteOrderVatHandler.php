<?php

namespace Backend\Modules\Commerce\Domain\OrderVat\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Commerce\Domain\OrderVat\OrderVatRepository;

final class DeleteOrderVatHandler
{
    /** @var OrderVatRepository */
    private $orderVatRepository;

    public function __construct(OrderVatRepository $orderVatRepository)
    {
        $this->orderVatRepository = $orderVatRepository;
    }

    public function handle(DeleteOrderVat $deleteOrderVat): void
    {
        $this->orderVatRepository->removeByIdAndLocale(
            $deleteOrderVat->orderVat->getId(),
            $deleteOrderVat->orderVat->getLocale()
        );

        Model::deleteExtraById($deleteOrderVat->orderVat->getExtraId());
    }
}

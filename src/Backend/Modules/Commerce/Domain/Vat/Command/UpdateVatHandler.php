<?php

namespace Backend\Modules\Commerce\Domain\Vat\Command;

use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\Commerce\Domain\Vat\VatRepository;

final class UpdateVatHandler
{
    /** @var VatRepository */
    private $vatRepository;

    public function __construct(VatRepository $vatRepository)
    {
        $this->vatRepository = $vatRepository;
    }

    public function handle(UpdateVat $updateVat): void
    {
        $vat = Vat::fromDataTransferObject($updateVat);
        $this->vatRepository->add($vat);

        $updateVat->setVatEntity($vat);
    }
}

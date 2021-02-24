<?php

namespace Backend\Modules\Commerce\Domain\Vat\Command;

use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\Commerce\Domain\Vat\VatRepository;

final class CreateVatHandler
{
    private VatRepository $vatRepository;

    public function __construct(VatRepository $vatRepository)
    {
        $this->vatRepository = $vatRepository;
    }

    public function handle(CreateVat $createVat): void
    {
        $createVat->sequence = $this->vatRepository->getNextSequence(
            $createVat->locale
        );

        $vat = Vat::fromDataTransferObject($createVat);
        $this->vatRepository->add($vat);

        $createVat->setVatEntity($vat);
    }
}

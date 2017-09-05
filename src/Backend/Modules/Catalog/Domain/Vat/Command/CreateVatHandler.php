<?php

namespace Backend\Modules\Catalog\Domain\Vat\Command;

use Backend\Modules\Catalog\Domain\Vat\Vat;
use Backend\Modules\Catalog\Domain\Vat\VatRepository;

final class CreateVatHandler
{
    /** @var VatRepository */
    private $vatRepository;

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

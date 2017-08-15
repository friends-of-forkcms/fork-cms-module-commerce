<?php

namespace Backend\Modules\Catalog\Domain\Vat\Command;

use Backend\Modules\Catalog\Domain\Vat\Vat;
use Backend\Modules\Catalog\Domain\Vat\VatRepository;

final class UpdateHandler
{
    /** @var VatRepository */
    private $vatRepository;

    public function __construct(VatRepository $vatRepository)
    {
        $this->vatRepository = $vatRepository;
    }

    public function handle(Update $updateVat): void
    {
        $vat = Vat::fromDataTransferObject($updateVat);
        $this->vatRepository->add($vat);

        $updateVat->setVatEntity($vat);
    }
}

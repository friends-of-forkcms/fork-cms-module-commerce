<?php

namespace Backend\Modules\Commerce\Domain\Vat\Command;

use Backend\Modules\Commerce\Domain\Vat\VatRepository;

final class DeleteVatHandler
{
    private VatRepository $vatRepository;

    public function __construct(VatRepository $vatRepository)
    {
        $this->vatRepository = $vatRepository;
    }

    public function handle(DeleteVat $deleteVat): void
    {
        $this->vatRepository->removeByIdAndLocale(
            $deleteVat->vat->getId(),
            $deleteVat->vat->getLocale()
        );
    }
}

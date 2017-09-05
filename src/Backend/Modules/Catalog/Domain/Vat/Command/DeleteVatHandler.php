<?php

namespace Backend\Modules\Catalog\Domain\Vat\Command;

use Backend\Modules\Catalog\Domain\Vat\VatRepository;

final class DeleteVatHandler
{
    /** @var VatRepository */
    private $vatRepository;

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

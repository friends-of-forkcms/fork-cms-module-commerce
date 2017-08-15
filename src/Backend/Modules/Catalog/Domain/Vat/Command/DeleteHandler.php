<?php

namespace Backend\Modules\Catalog\Domain\Vat\Command;

use Backend\Modules\Catalog\Domain\Vat\VatRepository;

final class DeleteHandler
{
    /** @var VatRepository */
    private $vatRepository;

    public function __construct(VatRepository $vatRepository)
    {
        $this->vatRepository = $vatRepository;
    }

    public function handle(Delete $deleteVat): void
    {
        $this->vatRepository->removeByIdAndLocale(
            $deleteVat->vat->getId(),
            $deleteVat->vat->getLocale()
        );
    }
}

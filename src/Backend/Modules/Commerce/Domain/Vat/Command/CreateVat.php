<?php

namespace Backend\Modules\Commerce\Domain\Vat\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\Commerce\Domain\Vat\VatDataTransferObject;

final class CreateVat extends VatDataTransferObject
{
    public function __construct(Locale $locale = null)
    {
        parent::__construct();

        if ($locale === null) {
            $locale = Locale::workingLocale();
        }

        $this->locale = $locale;
    }

    public function setVatEntity(Vat $vat): void
    {
        $this->vatEntity = $vat;
    }
}

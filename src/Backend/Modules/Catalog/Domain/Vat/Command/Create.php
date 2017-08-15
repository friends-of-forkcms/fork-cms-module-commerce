<?php

namespace Backend\Modules\Catalog\Domain\Vat\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Vat\Vat;
use Backend\Modules\Catalog\Domain\Vat\VatDataTransferObject;

final class Create extends VatDataTransferObject
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

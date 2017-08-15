<?php

namespace Backend\Modules\Catalog\Domain\Vat\Command;

use Backend\Modules\Catalog\Domain\Vat\Vat;
use Backend\Modules\Catalog\Domain\Vat\VatDataTransferObject;

final class Update extends VatDataTransferObject
{
    public function __construct(Vat $vat)
    {
        parent::__construct($vat);
    }

    public function setVatEntity(Vat $vatEntity): void
    {
        $this->vatEntity = $vatEntity;
    }
}

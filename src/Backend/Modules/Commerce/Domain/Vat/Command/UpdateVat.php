<?php

namespace Backend\Modules\Commerce\Domain\Vat\Command;

use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\Commerce\Domain\Vat\VatDataTransferObject;

final class UpdateVat extends VatDataTransferObject
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

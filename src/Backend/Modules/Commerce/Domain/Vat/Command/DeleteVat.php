<?php

namespace Backend\Modules\Commerce\Domain\Vat\Command;

use Backend\Modules\Commerce\Domain\Vat\Vat;

final class DeleteVat
{
    public Vat $vat;

    public function __construct(Vat $vat)
    {
        $this->vat = $vat;
    }
}

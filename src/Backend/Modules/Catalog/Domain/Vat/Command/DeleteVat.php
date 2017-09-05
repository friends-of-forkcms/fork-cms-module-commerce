<?php

namespace Backend\Modules\Catalog\Domain\Vat\Command;

use Backend\Modules\Catalog\Domain\Vat\Vat;

final class DeleteVat
{
    /** @var Vat */
    public $vat;

    public function __construct(Vat $vat)
    {
        $this->vat = $vat;
    }
}

<?php

namespace Backend\Modules\Catalog\Domain\Vat\Event;

use Backend\Modules\Catalog\Domain\Vat\Vat;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    /** @var Vat */
    private $vat;

    public function __construct(Vat $vat)
    {
        $this->vat = $vat;
    }

    public function getVat(): Vat
    {
        return $this->vat;
    }
}

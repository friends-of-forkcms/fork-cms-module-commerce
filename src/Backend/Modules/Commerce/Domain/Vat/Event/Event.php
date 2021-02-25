<?php

namespace Backend\Modules\Commerce\Domain\Vat\Event;

use Backend\Modules\Commerce\Domain\Vat\Vat;
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

<?php

namespace Backend\Modules\Commerce\Domain\Country\Event;

use Backend\Modules\Commerce\Domain\Country\Country;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    private Country $country;

    public function __construct(Country $country)
    {
        $this->country = $country;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }
}

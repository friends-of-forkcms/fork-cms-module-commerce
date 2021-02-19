<?php

namespace Backend\Modules\Catalog\Domain\Country\Event;

use Backend\Modules\Catalog\Domain\Country\Country;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    /** @var Country */
    private $country;

    public function __construct(Country $country)
    {
        $this->country = $country;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }
}

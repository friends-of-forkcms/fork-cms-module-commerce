<?php

namespace Backend\Modules\Commerce\Domain\Country\Command;

use Backend\Modules\Commerce\Domain\Country\Country;

final class DeleteCountry
{
    /** @var Country */
    public $country;

    public function __construct(Country $country)
    {
        $this->country = $country;
    }
}

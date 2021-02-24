<?php

namespace Backend\Modules\Commerce\Domain\Country\Command;

use Backend\Modules\Commerce\Domain\Country\Country;

final class DeleteCountry
{
    public Country $country;

    public function __construct(Country $country)
    {
        $this->country = $country;
    }
}

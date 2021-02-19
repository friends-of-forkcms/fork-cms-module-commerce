<?php

namespace Backend\Modules\Catalog\Domain\Country\Command;

use Backend\Modules\Catalog\Domain\Country\Country;

final class DeleteCountry
{
    /** @var Country */
    public $country;

    public function __construct(Country $country)
    {
        $this->country = $country;
    }
}

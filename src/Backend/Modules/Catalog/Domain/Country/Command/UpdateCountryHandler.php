<?php

namespace Backend\Modules\Catalog\Domain\Country\Command;

use Backend\Modules\Catalog\Domain\Country\Country;
use Backend\Modules\Catalog\Domain\Country\CountryRepository;

final class UpdateCountryHandler
{
    /** @var CountryRepository */
    private $countryRepository;

    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    public function handle(UpdateCountry $updateCountry): void
    {
        $country = Country::fromDataTransferObject($updateCountry);
        $this->countryRepository->add($country);

        $updateCountry->setCountryEntity($country);
    }
}

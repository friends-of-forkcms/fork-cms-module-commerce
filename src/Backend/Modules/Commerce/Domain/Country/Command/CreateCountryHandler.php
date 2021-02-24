<?php

namespace Backend\Modules\Commerce\Domain\Country\Command;

use Backend\Modules\Commerce\Domain\Country\Country;
use Backend\Modules\Commerce\Domain\Country\CountryRepository;

final class CreateCountryHandler
{
    private CountryRepository $countryRepository;

    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    public function handle(CreateCountry $createCountry): void
    {
        $country = Country::fromDataTransferObject($createCountry);
        $this->countryRepository->add($country);

        $createCountry->setCountryEntity($country);
    }
}

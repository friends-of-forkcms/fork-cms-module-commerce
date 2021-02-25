<?php

namespace Backend\Modules\Commerce\Domain\Country\Command;

use Backend\Modules\Commerce\Domain\Country\Country;
use Backend\Modules\Commerce\Domain\Country\CountryRepository;

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

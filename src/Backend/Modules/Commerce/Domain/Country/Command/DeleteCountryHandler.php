<?php

namespace Backend\Modules\Commerce\Domain\Country\Command;

use Backend\Modules\Commerce\Domain\Country\CountryRepository;

final class DeleteCountryHandler
{
    /** @var CountryRepository */
    private $countryRepository;

    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    public function handle(DeleteCountry $deleteCountry): void
    {
        $this->countryRepository->removeByIdAndLocale(
            $deleteCountry->country->getId(),
            $deleteCountry->country->getLocale()
        );
    }
}

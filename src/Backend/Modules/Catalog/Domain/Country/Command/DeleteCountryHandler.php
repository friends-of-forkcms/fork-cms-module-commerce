<?php

namespace Backend\Modules\Catalog\Domain\Country\Command;

use Backend\Modules\Catalog\Domain\Country\CountryRepository;

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

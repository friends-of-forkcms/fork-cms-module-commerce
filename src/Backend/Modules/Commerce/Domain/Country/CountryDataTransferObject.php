<?php

namespace Backend\Modules\Commerce\Domain\Country;

use Backend\Core\Language\Locale;
use Symfony\Component\Validator\Constraints as Assert;

class CountryDataTransferObject
{
    protected ?Country $countryEntity;
    public int $id;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $name;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $iso;
    public Locale $locale;

    public function __construct(Country $country = null)
    {
        $this->countryEntity = $country;
        $this->locale = Locale::workingLocale();

        if (!$this->hasExistingCountry()) {
            return;
        }

        $this->id = $this->countryEntity->getId();
        $this->name = $this->countryEntity->getName();
        $this->iso = $this->countryEntity->getIso();
        $this->locale = $this->countryEntity->getLocale();
    }

    public function setCountryEntity(Country $country): void
    {
        $this->countryEntity = $country;
    }

    public function getCountryEntity(): Country
    {
        return $this->countryEntity;
    }

    public function hasExistingCountry(): bool
    {
        return $this->countryEntity instanceof Country;
    }
}

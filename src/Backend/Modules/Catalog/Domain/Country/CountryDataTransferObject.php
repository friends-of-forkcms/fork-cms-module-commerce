<?php

namespace Backend\Modules\Catalog\Domain\Country;

use Backend\Core\Language\Locale;
use Symfony\Component\Validator\Constraints as Assert;

class CountryDataTransferObject
{
    /**
     * @var Country
     */
    protected $countryEntity;

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $name;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $iso;

    /**
     * @var Locale
     */
    public $locale;

    public function __construct(Country $country = null)
    {
        $this->countryEntity = $country;
        $this->locale = Locale::workingLocale();

        if (!$this->hasExistingCountry()) {
            return;
        }

        $this->id = $country->getId();
        $this->name = $country->getName();
        $this->iso = $country->getIso();
        $this->locale = $country->getLocale();
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

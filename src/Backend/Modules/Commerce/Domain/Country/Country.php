<?php

namespace Backend\Modules\Commerce\Domain\Country;

use Common\Locale;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="commerce_countries")
 * @ORM\Entity(repositoryClass="CountryRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Country
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @var Locale
     *
     * @ORM\Column(type="locale", name="language")
     */
    private $locale;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $iso;

    private function __construct(
        Locale $locale,
        string $name,
        string $iso
    )
    {
        $this->locale = $locale;
        $this->name = $name;
        $this->iso = $iso;
    }

    public static function fromDataTransferObject(CountryDataTransferObject $dataTransferObject)
    {
        if ($dataTransferObject->hasExistingCountry()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(CountryDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->locale,
            $dataTransferObject->name,
            $dataTransferObject->iso
        );
    }

    private static function update(CountryDataTransferObject $dataTransferObject)
    {
        $country = $dataTransferObject->getCountryEntity();

        $country->locale = $dataTransferObject->locale;
        $country->name = $dataTransferObject->name;
        $country->iso = $dataTransferObject->iso;

        return $country;
    }

    public function getDataTransferObject(): CountryDataTransferObject
    {
        return new CountryDataTransferObject($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getIso(): string
    {
        return $this->iso;
    }
}

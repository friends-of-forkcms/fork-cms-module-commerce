<?php

namespace Backend\Modules\Commerce\Domain\Country;

use Common\Locale;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="commerce_countries")
 * @ORM\Entity(repositoryClass="CountryRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Country
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\Column(type="locale", name="language")
     */
    private Locale $locale;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $iso;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="created_on", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdOn;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", name="edited_on", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $editedOn;

    private function __construct(
        Locale $locale,
        string $name,
        string $iso
    ) {
        $this->locale = $locale;
        $this->name = $name;
        $this->iso = $iso;
    }

    public static function fromDataTransferObject(CountryDataTransferObject $dataTransferObject): Country
    {
        if ($dataTransferObject->hasExistingCountry()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(CountryDataTransferObject $dataTransferObject): Country
    {
        return new self(
            $dataTransferObject->locale,
            $dataTransferObject->name,
            $dataTransferObject->iso
        );
    }

    private static function update(CountryDataTransferObject $dataTransferObject): Country
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getIso(): string
    {
        return $this->iso;
    }
}

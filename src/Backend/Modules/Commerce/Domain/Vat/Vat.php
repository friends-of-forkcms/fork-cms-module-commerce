<?php

namespace Backend\Modules\Commerce\Domain\Vat;

use Common\Locale;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="commerce_vats")
 * @ORM\Entity(repositoryClass="VatRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Vat
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
    private string $title;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private float $percentage;

    /**
     * @ORM\Column(type="integer", length=11)
     */
    private int $sequence;

    private function __construct(
        Locale $locale,
        string $title,
        float $percentage,
        int $sequence
    ) {
        $this->locale = $locale;
        $this->title = $title;
        $this->percentage = $percentage;
        $this->sequence = $sequence;
    }

    public static function fromDataTransferObject(VatDataTransferObject $dataTransferObject): Vat
    {
        if ($dataTransferObject->hasExistingVat()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(VatDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->locale,
            $dataTransferObject->title,
            $dataTransferObject->percentage,
            $dataTransferObject->sequence
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getPercentage(): int
    {
        return $this->percentage;
    }

    /**
     * Get the VAT percentage as a real percentage value.
     */
    public function getAsPercentage(): float
    {
        return $this->percentage / 100;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function setSequence(int $sequence): void
    {
        $this->sequence = $sequence;
    }

    private static function update(VatDataTransferObject $dataTransferObject): Vat
    {
        $vat = $dataTransferObject->getVatEntity();

        $vat->locale = $dataTransferObject->locale;
        $vat->title = $dataTransferObject->title;
        $vat->percentage = $dataTransferObject->percentage;
        $vat->sequence = $dataTransferObject->sequence;

        return $vat;
    }

    public function getDataTransferObject(): VatDataTransferObject
    {
        return new VatDataTransferObject($this);
    }
}

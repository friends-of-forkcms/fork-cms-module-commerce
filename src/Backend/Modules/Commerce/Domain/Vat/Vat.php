<?php

namespace Backend\Modules\Commerce\Domain\Vat;

use Common\Locale;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Money\Money;

/**
 * @ORM\Table(name="commerce_vats")
 * @ORM\Entity(repositoryClass="VatRepository")
 */
class Vat
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @Gedmo\SortableGroup
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
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer", length=11)
     */
    private ?int $sequence;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $updatedAt;

    private function __construct(
        Locale $locale,
        string $title,
        float $percentage,
        ?int $sequence
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

    public function calculateVatFor(Money $amount): Money
    {
        if ($this->percentage === 0.0 || $amount->isZero()) {
            return new Money(0, $amount->getCurrency());
        }

        return $amount->multiply($this->percentage / 100, Money::ROUND_HALF_DOWN);
    }

    public function calculateInclusiveAmountFor(Money $amount): money
    {
        return $amount->add($this->calculateVatFor($amount));
    }

    public function getSequence(): ?int
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

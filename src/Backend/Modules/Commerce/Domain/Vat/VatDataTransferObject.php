<?php

namespace Backend\Modules\Commerce\Domain\Vat;

use Common\Locale;
use Symfony\Component\Validator\Constraints as Assert;

class VatDataTransferObject
{
    protected ?Vat $vatEntity;
    public int $id;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $title;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public int $percentage;
    public Locale $locale;
    public ?int $sequence;
    public int $type;

    public function __construct(Vat $vat = null)
    {
        $this->vatEntity = $vat;
        $this->sequence = null;

        if (!$this->hasExistingVat()) {
            return;
        }

        $this->id = $this->vatEntity->getId();
        $this->title = $this->vatEntity->getTitle();
        $this->percentage = $this->vatEntity->getPercentage();
        $this->locale = $this->vatEntity->getLocale();
        $this->sequence = $this->vatEntity->getSequence();
    }

    public function getVatEntity(): Vat
    {
        return $this->vatEntity;
    }

    public function hasExistingVat(): bool
    {
        return $this->vatEntity instanceof Vat;
    }
}

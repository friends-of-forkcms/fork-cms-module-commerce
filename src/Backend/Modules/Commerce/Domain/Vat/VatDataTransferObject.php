<?php

namespace Backend\Modules\Commerce\Domain\Vat;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\VatValue\VatValue;
use Symfony\Component\Validator\Constraints as Assert;

class VatDataTransferObject
{
    /**
     * @var Vat
     */
    protected $vatEntity;

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $title;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $percentage;

    /**
     * @var Locale
     */
    public $locale;

    /**
     * @var int
     */
    public $sequence;

    /**
     * @var int
     */
    public $type;

    public function __construct(Vat $vat = null)
    {
        $this->vatEntity = $vat;

        if (! $this->hasExistingVat()) {
            return;
        }

        $this->id         = $vat->getId();
        $this->title      = $vat->getTitle();
        $this->percentage = $vat->getPercentage();
        $this->locale     = $vat->getLocale();
        $this->sequence   = $vat->getSequence();
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

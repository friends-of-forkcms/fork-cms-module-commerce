<?php

namespace Backend\Modules\Commerce\Domain\StockStatus;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\StockStatusValue\StockStatusValue;
use Symfony\Component\Validator\Constraints as Assert;

class StockStatusDataTransferObject
{
    /**
     * @var StockStatus
     */
    protected $stockStatusEntity;

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
     * @var Locale
     */
    public $locale;

    /**
     * @var int
     */
    public $type;

    public function __construct(StockStatus $stockStatus = null)
    {
        $this->stockStatusEntity = $stockStatus;

        if (! $this->hasExistingStockStatus()) {
            return;
        }

        $this->id       = $stockStatus->getId();
        $this->title    = $stockStatus->getTitle();
        $this->locale   = $stockStatus->getLocale();
    }

    public function getStockStatusEntity(): StockStatus
    {
        return $this->stockStatusEntity;
    }

    public function hasExistingStockStatus(): bool
    {
        return $this->stockStatusEntity instanceof StockStatus;
    }
}

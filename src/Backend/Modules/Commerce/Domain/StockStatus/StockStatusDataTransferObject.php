<?php

namespace Backend\Modules\Commerce\Domain\StockStatus;

use Common\Locale;
use Symfony\Component\Validator\Constraints as Assert;

class StockStatusDataTransferObject
{
    protected ?StockStatus $stockStatusEntity;
    public int $id;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $title;
    public Locale $locale;
    public int $type;

    public function __construct(StockStatus $stockStatus = null)
    {
        $this->stockStatusEntity = $stockStatus;

        if (!$this->hasExistingStockStatus()) {
            return;
        }

        $this->id = $this->stockStatusEntity->getId();
        $this->title = $this->stockStatusEntity->getTitle();
        $this->locale = $this->stockStatusEntity->getLocale();
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

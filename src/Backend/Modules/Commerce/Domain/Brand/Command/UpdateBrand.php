<?php

namespace Backend\Modules\Commerce\Domain\Brand\Command;

use Backend\Modules\Commerce\Domain\Brand\Brand;
use Backend\Modules\Commerce\Domain\Brand\BrandDataTransferObject;

final class UpdateBrand extends BrandDataTransferObject
{
    public function __construct(Brand $brand)
    {
        parent::__construct($brand);
    }

    public function setBrandEntity(Brand $brandEntity): void
    {
        $this->brandEntity = $brandEntity;
    }
}

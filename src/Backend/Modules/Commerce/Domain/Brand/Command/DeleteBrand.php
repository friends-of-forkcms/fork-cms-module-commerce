<?php

namespace Backend\Modules\Commerce\Domain\Brand\Command;

use Backend\Modules\Commerce\Domain\Brand\Brand;

final class DeleteBrand
{
    public Brand $brand;

    public function __construct(Brand $brand)
    {
        $this->brand = $brand;
    }
}

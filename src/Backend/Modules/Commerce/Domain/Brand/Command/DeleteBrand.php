<?php

namespace Backend\Modules\Commerce\Domain\Brand\Command;

use Backend\Modules\Commerce\Domain\Brand\Brand;

final class DeleteBrand
{
    /** @var Brand */
    public $brand;

    public function __construct(Brand $brand)
    {
        $this->brand = $brand;
    }
}

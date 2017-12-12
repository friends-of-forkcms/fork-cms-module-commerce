<?php

namespace Backend\Modules\Catalog\Domain\Brand\Command;

use Backend\Modules\Catalog\Domain\Brand\Brand;

final class DeleteBrand
{
    /** @var Brand */
    public $brand;

    public function __construct(Brand $brand)
    {
        $this->brand = $brand;
    }
}

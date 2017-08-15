<?php

namespace Backend\Modules\Catalog\Domain\Brand\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Brand\Brand;
use Backend\Modules\Catalog\Domain\Brand\BrandDataTransferObject;

final class Create extends BrandDataTransferObject
{
    public function __construct(Locale $locale = null)
    {
        parent::__construct();

        if ($locale === null) {
            $locale = Locale::workingLocale();
        }

        $this->locale = $locale;
    }

    public function setBrandEntity(Brand $brand): void
    {
        $this->brandEntity = $brand;
    }
}

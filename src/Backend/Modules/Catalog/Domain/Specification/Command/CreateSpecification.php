<?php

namespace Backend\Modules\Catalog\Domain\Specification\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Specification\Specification;
use Backend\Modules\Catalog\Domain\Specification\SpecificationDataTransferObject;

final class CreateSpecification extends SpecificationDataTransferObject
{
    public function __construct(Locale $locale = null)
    {
        parent::__construct();

        if ($locale === null) {
            $locale = Locale::workingLocale();
        }

        $this->locale = $locale;
    }

    public function setSpecificationEntity(Specification $specification): void
    {
        $this->specificationEntity = $specification;
    }
}

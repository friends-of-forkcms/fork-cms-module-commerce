<?php

namespace Backend\Modules\Commerce\Domain\Specification\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Specification\Specification;
use Backend\Modules\Commerce\Domain\Specification\SpecificationDataTransferObject;

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

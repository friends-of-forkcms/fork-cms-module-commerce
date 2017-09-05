<?php

namespace Backend\Modules\Catalog\Domain\Category\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Category\Category;
use Backend\Modules\Catalog\Domain\Category\CategoryDataTransferObject;

final class CreateCategory extends CategoryDataTransferObject
{
    public function __construct(Locale $locale = null)
    {
        parent::__construct();

        if ($locale === null) {
            $locale = Locale::workingLocale();
        }

        $this->locale = $locale;
    }

    public function setCategoryEntity(Category $category): void
    {
        $this->categoryEntity = $category;
    }
}

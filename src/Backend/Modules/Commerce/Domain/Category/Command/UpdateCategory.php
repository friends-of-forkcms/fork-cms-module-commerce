<?php

namespace Backend\Modules\Commerce\Domain\Category\Command;

use Backend\Modules\Commerce\Domain\Category\Category;
use Backend\Modules\Commerce\Domain\Category\CategoryDataTransferObject;

final class UpdateCategory extends CategoryDataTransferObject
{
    public function setCategoryEntity(Category $categoryEntity): void
    {
        $this->categoryEntity = $categoryEntity;
    }
}

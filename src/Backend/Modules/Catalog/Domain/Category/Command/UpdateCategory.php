<?php

namespace Backend\Modules\Catalog\Domain\Category\Command;

use Backend\Modules\Catalog\Domain\Category\Category;
use Backend\Modules\Catalog\Domain\Category\CategoryDataTransferObject;

final class UpdateCategory extends CategoryDataTransferObject
{
    public function __construct(Category $category)
    {
        parent::__construct($category);
    }

    public function setCategoryEntity(Category $categoryEntity): void
    {
        $this->categoryEntity = $categoryEntity;
    }
}

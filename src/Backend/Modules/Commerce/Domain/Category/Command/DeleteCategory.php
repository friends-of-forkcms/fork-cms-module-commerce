<?php

namespace Backend\Modules\Commerce\Domain\Category\Command;

use Backend\Modules\Commerce\Domain\Category\Category;

final class DeleteCategory
{
    public Category $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }
}

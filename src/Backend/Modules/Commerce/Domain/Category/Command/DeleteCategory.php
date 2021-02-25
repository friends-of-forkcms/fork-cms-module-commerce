<?php

namespace Backend\Modules\Commerce\Domain\Category\Command;

use Backend\Modules\Commerce\Domain\Category\Category;

final class DeleteCategory
{
    /** @var Category */
    public $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }
}

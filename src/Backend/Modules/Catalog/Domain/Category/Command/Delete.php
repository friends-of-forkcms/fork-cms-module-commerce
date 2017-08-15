<?php

namespace Backend\Modules\Catalog\Domain\Category\Command;

use Backend\Modules\Catalog\Domain\Category\Category;

final class Delete
{
    /** @var Category */
    public $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }
}

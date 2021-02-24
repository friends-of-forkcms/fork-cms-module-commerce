<?php

namespace Backend\Modules\Commerce\Domain\Category\Event;

use Backend\Modules\Commerce\Domain\Category\Category;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    private Category $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }
}

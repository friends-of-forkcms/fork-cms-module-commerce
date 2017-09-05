<?php

namespace Backend\Modules\Catalog\Domain\Category\Command;

use Backend\Modules\Catalog\Domain\Category\Category;
use Backend\Modules\Catalog\Domain\Category\CategoryRepository;

final class UpdateCategoryHandler
{
    /** @var CategoryRepository */
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function handle(UpdateCategory $updateCategory): void
    {
        $category = Category::fromDataTransferObject($updateCategory);
        $this->categoryRepository->add($category);

        $updateCategory->setCategoryEntity($category);
    }
}

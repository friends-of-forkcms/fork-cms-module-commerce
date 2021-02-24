<?php

namespace Backend\Modules\Commerce\Domain\Category\Command;

use Backend\Modules\Commerce\Domain\Category\Category;
use Backend\Modules\Commerce\Domain\Category\CategoryRepository;

final class UpdateCategoryHandler
{
    private CategoryRepository $categoryRepository;

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

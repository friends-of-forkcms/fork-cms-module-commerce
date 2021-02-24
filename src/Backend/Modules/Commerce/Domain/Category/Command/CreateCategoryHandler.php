<?php

namespace Backend\Modules\Commerce\Domain\Category\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Commerce\Domain\Category\Category;
use Backend\Modules\Commerce\Domain\Category\CategoryRepository;
use Common\ModuleExtraType;

final class CreateCategoryHandler
{
    private CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function handle(CreateCategory $createCategory): void
    {
        $createCategory->extraId = $this->getNewExtraId();
        $createCategory->sequence = $this->categoryRepository->getNextSequence(
            $createCategory->locale,
            $createCategory->parent
        );

        $category = Category::fromDataTransferObject($createCategory);
        $this->categoryRepository->add($category);

        $createCategory->setCategoryEntity($category);
    }

    private function getNewExtraId(): int
    {
        return Model::insertExtra(
            ModuleExtraType::widget(),
            'Commerce',
            'Category'
        );
    }
}

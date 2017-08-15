<?php

namespace Backend\Modules\Catalog\Domain\Category\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Catalog\Domain\Category\Category;
use Backend\Modules\Catalog\Domain\Category\CategoryRepository;
use Common\ModuleExtraType;

final class CreateHandler
{
    /** @var CategoryRepository */
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function handle(Create $createCategory): void
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
            'Catalog',
            'Category'
        );
    }
}

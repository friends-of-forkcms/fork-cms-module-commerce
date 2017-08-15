<?php

namespace Backend\Modules\Catalog\Domain\Category\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Catalog\Domain\Category\CategoryRepository;

final class DeleteHandler
{
    /** @var CategoryRepository */
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function handle(Delete $deleteCategory): void
    {
        $this->categoryRepository->removeByIdAndLocale(
            $deleteCategory->category->getId(),
            $deleteCategory->category->getLocale()
        );

        Model::deleteExtraById($deleteCategory->category->getExtraId());
    }
}

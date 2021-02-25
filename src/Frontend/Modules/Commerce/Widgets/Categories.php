<?php

namespace Frontend\Modules\Commerce\Widgets;

use Backend\Modules\Commerce\Domain\Category\CategoryRepository;
use Backend\Modules\Commerce\Domain\Product\ProductRepository;
use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Core\Language\Locale;

/**
 * This is a widget with the Commerce-categories
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Categories extends FrontendBaseWidget
{
    /**
     * Execute the extra
     */
    public function execute(): void
    {
        parent::execute();
        $this->loadTemplate();

        $categoryRepository = $this->getCategoryRepository();
        $productRepository = $this->getProductRepository();

        $parameters = $this->url->getParameters(false);
        $parameterCount = count($parameters);
        $activeCategory = null;
        $activeProduct = null;

        if ($parameterCount >= 3) { // Parent category, category and product
            if ($product = $productRepository->findByCategoryAndUrl(
                Locale::frontendLanguage(),
                $parameters[1],
                $parameters[2]
            )) {
                $activeProduct = $product;
                $activeCategory = $product->getCategory();
            }
        } elseif ($parameterCount == 2) {
            if ($product = $productRepository->findByCategoryAndUrl(Locale::frontendLanguage(), $parameters[0],
                $parameters[1])) {
                $activeProduct = $product;
                $activeCategory = $product->getCategory();
            } elseif ($category = $categoryRepository->findByLocaleAndUrl(Locale::frontendLanguage(), $parameters[1])) {
                $activeCategory = $category;
            }
        } elseif (
            $parameterCount == 1 && (
            $category = $categoryRepository->findByLocaleAndUrl(Locale::frontendLanguage(), $parameters[0])
            )
        ) {
            $activeCategory = $category;
        }

        $this->template->assign('activeCategory', $activeCategory);
        $this->template->assign('activeProduct', $activeProduct);
        $this->template->assign('categories', $categoryRepository->findParents(Locale::frontendLanguage()));
    }

    private function getCategoryRepository(): CategoryRepository
    {
        return $this->get('commerce.repository.category');
    }

    /**
     * @return ProductRepository
     */
    private function getProductRepository(): ProductRepository
    {
        return $this->get('commerce.repository.product');
    }
}

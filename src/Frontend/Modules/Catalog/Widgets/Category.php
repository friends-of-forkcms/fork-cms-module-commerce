<?php

namespace Frontend\Modules\Catalog\Widgets;

use Backend\Modules\Catalog\Domain\Category\CategoryRepository;
use Backend\Modules\Catalog\Domain\Category\Exception\CategoryNotFound;
use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\Product\ProductRepository;
use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Core\Language\Locale;

/**
 * This is a widget with the Catalog-categories
 *
 * @author Waldo Cosman <waldo@comsa.be>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Category extends FrontendBaseWidget
{
    /**
     * @var \Backend\Modules\Catalog\Domain\Category\Category
     */
    private $category;

    /**
     * @var Product[]
     */
    private $products;

    /**
     * Execute the extra
     */
    public function execute(): void
    {
        parent::execute();
        $this->loadData();

        $this->loadTemplate();
        $this->parse();
    }

    /**
     * Load the data
     */
    private function loadData()
    {
        try {
            $this->category = $this->getCategoryRepository()->findOneByIdAndLocale(
                $this->data['id'],
                Locale::frontendLanguage()
            );

            $this->products = $this->getProductRepository()->findLimitedByCategory(
                $this->category,
                $this->get('fork.settings')->get($this->getModule(), 'products_in_widget', 6)
            );
        } catch (CategoryNotFound $e) {
            $this->category = null;
        }
    }

    /**
     * Parse
     */
    private function parse()
    {
        // assign comments
        $this->template->assign('category', $this->category);
        $this->template->assign('products', $this->products);
    }

    /**
     * Get the category repository
     *
     * @return CategoryRepository
     */
    private function getCategoryRepository(): CategoryRepository
    {
        return $this->get('catalog.repository.category');
    }

    /**
     * Get the product repository
     *
     * @return ProductRepository
     */
    private function getProductRepository(): ProductRepository
    {
        return $this->get('catalog.repository.product');
    }
}

<?php

namespace Frontend\Modules\Catalog\Widgets;

use Backend\Modules\Catalog\Domain\Category\CategoryRepository;
use Backend\Modules\Catalog\Domain\Category\Exception\CategoryNotFound;
use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Core\Language\Locale;
use Frontend\Modules\Catalog\Engine\Model as FrontendCatalogModel;

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
}

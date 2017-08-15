<?php

namespace Frontend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Modules\Catalog\Domain\Category\CategoryRepository;
use Frontend\Core\Engine\Base\Block as FrontendBaseBlock;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Category\Category as CategoryEntity;

/**
 * This is the category-action, it will display the overview of products/subcategories within a category
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Category extends FrontendBaseBlock
{
    /**
     * The category
     *
     * @var CategoryEntity
     */
    private $category;

    /**
     * The pagination array
     * It will hold all needed parameters, some of them need initialization.
     *
     * @var    array
     */
    protected $pagination = [
        'limit'          => 10,
        'offset'         => 0,
        'requested_page' => 1,
        'num_items'      => null,
        'num_pages'      => null
    ];

    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();
        $this->loadTemplate();
        $this->getData();
        $this->parse();
    }

    /**
     * Load the data, don't forget to validate the incoming data
     */
    private function getData()
    {
        $this->getCategory();
        $this->buildPagination();
    }

    /**
     * Get current category
     */
    private function getCategory()
    {
        $parameters = $this->url->getParameters();
        $url        = end($parameters);

        if ($url === null) {
            $this->redirect(FrontendNavigation::getURL(404));
        }

        /**
         * @var CategoryRepository
         */
        $categoryRepository = $this->get('catalog.repository.category');

        if ( ! $this->category = $categoryRepository->findByLocaleAndUrl(Locale::frontendLanguage(), $url)) {
            $this->redirect(FrontendNavigation::getURL(404));
        }
    }

    /**
     * Build pagination data
     */
    private function buildPagination()
    {
        // requested page
        $requestedPage = $this->url->getParameter('page', 'int', 1);

        // set URL and limit
        $this->pagination['url'] = FrontendNavigation::getURLForBlock('Catalog',
                'Category') . '/' . $this->category->getMeta()->getUrl();

        $this->pagination['limit'] = $this->get('fork.settings')->get('catalog', 'overview_num_items', 10);

        // populate count fields in pagination
        $this->pagination['num_items'] = $this->category->getChildren()->count();
        $this->pagination['num_pages'] = (int)ceil($this->pagination['num_items'] / $this->pagination['limit']);

        // num pages is always equal to at least 1
        if ($this->pagination['num_pages'] == 0) {
            $this->pagination['num_pages'] = 1;
        }

        // redirect if the request page doesn't exist
        if ($requestedPage > $this->pagination['num_pages'] || $requestedPage < 1) {
            $this->redirect(FrontendNavigation::getURL(404));
        }

        // populate calculated fields in pagination
        $this->pagination['requested_page'] = $requestedPage;
        $this->pagination['offset']         = ($this->pagination['requested_page'] * $this->pagination['limit']) - $this->pagination['limit'];
    }

    /**
     * A recursive function to add the required breadcrumbs
     *
     * @param CategoryEntity $category
     *
     * @return string
     */
    private function addBreadcrumb(CategoryEntity $category): string
    {
        if ($category->getParent()) {
            $url = $this->addBreadcrumb($category->getParent());
        } else {
            $url = FrontendNavigation::getURLForBlock('Catalog', 'Category');
        }

        $url .= '/' . $category->getMeta()->getUrl();

        $this->breadcrumb->addElement(
            $category->getTitle(),
            $url
        );

        return $url;
    }

    /**
     * Parse the page
     */
    protected function parse()
    {
        // add css
        $this->header->addCSS('/src/Frontend/Modules/' . $this->getModule() . '/Layout/Css/catalog.css');

        // add noty js
        $this->header->addJS('/src/Frontend/Modules/' . $this->getModule() . '/Js/noty/packaged/jquery.noty.packaged.min.js');

        // add breadcrumbs
        $categoryUrl = $this->addBreadcrumb($this->category);

        // overwrite the meta
        $this->setMeta($this->category->getMeta());

        // assign the category
        $this->template->assign('category', $this->category);

        // assign the products base url
        $this->template->assign('productBaseUrl', FrontendNavigation::getUrlForBlock('Catalog', 'Detail'));
        $this->template->assign('categoryBaseUrl', $categoryUrl);

        // parse the pagination
        $this->parsePagination();
    }
}

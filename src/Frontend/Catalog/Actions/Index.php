<?php

namespace Frontend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Modules\Catalog\Domain\Category\CategoryRepository;
use Backend\Modules\Catalog\Domain\Product\ProductRepository;
use Frontend\Core\Engine\Base\Block as FrontendBaseBlock;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Core\Language\Locale;

/**
 * This is the overview-action
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Index extends FrontendBaseBlock
{
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
        $this->buildPagination();
        $this->getCategories();
        $this->getProducts();
    }

    /**
     * Build the pagination
     */
    private function buildPagination()
    {
        /**
         * @var ProductRepository
         */
        $productRepository = $this->get('catalog.repository.product');

        // requested page
        $requestedPage = $this->url->getParameter('page', 'int', 1);

        // set URL and limit
        $this->pagination['url']   = FrontendNavigation::getURLForBlock('Catalog');
        $this->pagination['limit'] = $this->get('fork.settings')->get('catalog', 'overview_num_items', 10);

        // populate count fields in pagination
        $this->pagination['num_items'] = $productRepository->getCount(Locale::frontendLanguage());
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
     * Get all categories
     */
    private function getCategories()
    {
        /**
         * @var CategoryRepository
         */
        $categoryRepository = $this->get('catalog.repository.category');

        $this->template->assign('categories', $categoryRepository->findParents(Locale::frontendLanguage()));
        $this->template->assign('categoriesBaseUrl', FrontendNavigation::getURLForBlock('Catalog', 'Category'));
    }

    /**
     * Get all products
     */
    private function getProducts()
    {
        /**
         * @var ProductRepository
         */
        $productRepository = $this->get('catalog.repository.product');

        $this->template->assign(
            'products',
            $productRepository->findLimited(
                Locale::frontendLanguage(),
                $this->pagination['limit'],
                $this->pagination['offset']
            )
        );

        $this->template->assign('productBaseUrl', FrontendNavigation::getURLForBlock('Catalog', 'Detail'));
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

        // parse the pagination
        $this->parsePagination();
    }
}

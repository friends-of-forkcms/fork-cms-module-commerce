<?php

namespace Frontend\Modules\Catalog\Actions;

use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\Product\ProductRepository;
use Backend\Modules\Catalog\Domain\Search\SearchDataTransferObject;
use Backend\Modules\Catalog\Domain\Search\SearchType;
use Backend\Modules\Catalog\Domain\Specification\SpecificationRepository;
use Common\Exception\RedirectException;
use Frontend\Core\Engine\Base\Block as FrontendBaseBlock;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Core\Engine\Navigation;
use Frontend\Core\Language\Language;
use Frontend\Core\Language\Locale;
use Frontend\Modules\Catalog\Engine\Pagination;
use Symfony\Component\Form\Form;

/**
 * Search the products
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Search extends FrontendBaseBlock
{
    /**
     * Execute the action
     *
     * @throws RedirectException
     */
    public function execute(): void
    {
        parent::execute();

        $parameters = $this->url->getParameters(false);
        $parameterCount = count($parameters);

        if ($parameterCount == 0) { // Overview
            $this->parseSearch();
        } else {
            $this->redirect(FrontendNavigation::getUrl(404));
        }
    }

    /**
     * Parse products overview in a category
     *
     * @throws RedirectException
     */
    private function parseSearch()
    {
        // Start loading the template
        $this->loadTemplate();

        // Get the search form
        $form = $this->getSearchForm();

        // Check if the form has been submitted, if so redirect to change the URL
        if ($form->isSubmitted() && $form->isValid()) {
            $this->redirect(
                Navigation::getUrlForBlock(
                    'Catalog',
                    'Search'
                ) . '?query=' . $form->getData()->query
            );
        }

        // Assign the form to our view
        $this->template->assign('form', $form->createView());

        // When there is no search query stop executing everything else
        if (!$form->getData()->query) {
            return;
        }

        // Set some default variables
        $currentPage = $this->url->getParameter(Language::lbl('Page'), 'int', 1);
        $itemsPerPage = $this->get('fork.settings')->get('Catalog', 'overview_num_items', 10);
        $filtersShowMoreCount = $this->get('fork.settings')->get('Catalog', 'filters_show_more_num_items', 5);
        $productRepository = $this->getProductRepository();
        $specificationRepository = $this->getSpecificationRepository();
        $productOffset = ($currentPage - 1) * $itemsPerPage;
        $baseUrl = '/' . implode('/', array_merge($this->url->getPages()));

        // Build pagination
        $pagination = new Pagination();
        $pagination->setCurrentPage($currentPage);
        $pagination->setItemsPerPage($itemsPerPage);
        $pagination->setBaseUrl($baseUrl);
        $pagination->setParameters($this->getRequest()->query->all());

        // Define the sort orders
        $sortOrders = [
            Product::SORT_RANDOM => [
                'label' => 'Willekeurig',
                'selected' => false,
            ],
            Product::SORT_PRICE_ASC => [
                'label' => 'Prijs (laag/hoog)',
                'selected' => false,
            ],
            Product::SORT_PRICE_DESC => [
                'label' => 'Prijs (hoog/laag)',
                'selected' => false,
            ],
            Product::SORT_CREATED_AT => [
                'label' => 'Toegevoegd',
                'selected' => false,
            ]
        ];

        $currentSortOrder = $this->getRequest()->get('sort', Product::SORT_RANDOM);
        if (array_key_exists($currentSortOrder, $sortOrders)) {
            $sortOrders[$currentSortOrder]['selected'] = true;
        }

        // Get the filters for current category
        $filters = $specificationRepository->findFiltersBySearchTerm($form->getData()->query);

        // Fetch the products
        if ($productFilters = $this->getProductFilters()) {
            $products = $productRepository->filterSearchedProducts(
                $form->getData()->query,
                $productFilters,
                $itemsPerPage,
                $productOffset,
                $currentSortOrder
            );

            $productCount = $productRepository->filterSearchedProductsCount(
                $form->getData()->query,
                $productFilters
            );
        } else {
            $products = $productRepository->searchProductsLimited(
                $form->getData()->query,
                $itemsPerPage,
                $productOffset,
                $currentSortOrder
            );

            $productCount = $productRepository->getSearchProductCount(
                $form->getData()->query,
                Locale::frontendLanguage()
            );
        }

        // Set the item count for the pagination
        $pagination->setItemCount($productCount);

        // When requesting an invalid page return to 404
        if ($currentPage > $pagination->getPageCount() || $currentPage < 1) {
            $this->redirect(
                FrontendNavigation::getUrl(404)
            );
        }

        // Set JS values
        $this->addJSData('filterUrl', $baseUrl);
        $this->addJSData('searchTerm', $form->getData()->query);
        $this->addJS('Filter.js');
        $this->addJS('EnhancedEcommerce.js');

        // Assign to our template
        $this->template->assign('searchTerm', $form->getData()->query);
        $this->template->assign('products', $products);
        $this->template->assign('productCount', $productCount);
        $this->template->assign('pagination', $pagination);
        $this->template->assign('filters', $filters);
        $this->template->assign('sortOrders', $sortOrders);
        $this->template->assign('filtersShowMoreCount', $filtersShowMoreCount);
    }

    /**
     * Load the search form
     *
     * @return Form
     */
    private function getSearchForm(): Form
    {
        $form = $this->createForm(
            SearchType::class,
            new SearchDataTransferObject($this->getRequest()),
            [
                'action' => '/' . implode('/', array_merge($this->url->getPages())),
            ]
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    /**
     * @return ProductRepository
     */
    private function getProductRepository(): ProductRepository
    {
        return $this->get('catalog.repository.product');
    }

    /**
     * @return SpecificationRepository
     */
    private function getSpecificationRepository(): SpecificationRepository
    {
        return $this->get('catalog.repository.specification');
    }

    /**
     * Get an array with the filters which could be used
     *
     * @return array
     */
    private function getProductFilters(): array
    {
        $filters = [];

        foreach ($this->getRequest()->query->all() as $key => $value) {
            if ($this->isExcludedFromFilter($key)) {
                continue;
            }

            $filters[$key] = explode(',', $value);
        }

        return $filters;
    }

    /**
     * Check if query part is excluded from filters
     *
     * @param string $key
     *
     * @return boolean
     */
    private function isExcludedFromFilter(string $key): bool
    {
        $excludedKeys = [
            Language::lbl('Page'),
            'sort',
            'query',
        ];

        return in_array($key, $excludedKeys);
    }
}

<?php

namespace Frontend\Modules\Catalog\Ajax;

use Backend\Modules\Catalog\Domain\Category\CategoryRepository;
use Backend\Modules\Catalog\Domain\Category\Exception\CategoryNotFound;
use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\Product\ProductRepository;
use Frontend\Core\Engine\Base\AjaxAction as FrontendBaseAJAXAction;
use Frontend\Core\Language\Locale;
use Frontend\Modules\Catalog\Engine\Pagination;
use Symfony\Component\HttpFoundation\Response;

class FilterProducts extends FrontendBaseAJAXAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        parent::execute();

        $productRepository = $this->getProductRepository();
        $categoryRepository = $this->getCatalogRepository();
        $locale = Locale::frontendLanguage();
        $itemsPerPage = $this->get('fork.settings')->get('Catalog', 'overview_num_items', 10);
        $currentPage = $this->getRequest()->get('page', 1);
        $productOffset = ($currentPage - 1) * $itemsPerPage;

        // Get the category
        try {
            $category = $categoryRepository->findOneByIdAndLocale($this->getRequest()->get('category'), $locale);
        } catch (CategoryNotFound $e) {
            $this->output(Response::HTTP_NOT_FOUND);
            return;
        }

        // Build pagination
        $pagination = new Pagination();
        $pagination->setCurrentPage($currentPage);
        $pagination->setItemsPerPage($itemsPerPage);
        $pagination->setBaseUrl($category->getUrl());

        // Filter the products
        if ($filters = $this->getRequest()->get('filters')) {
            $products = $productRepository->filterProducts($filters, $category, $itemsPerPage, $productOffset, $this->getRequest()->get('sort', Product::SORT_RANDOM));
            $pagination->setItemCount($productRepository->filterProductsCount($filters, $category));
        } else {
            $products = $productRepository->findLimitedByCategory($category, $itemsPerPage, $productOffset, $this->getRequest()->get('sort', Product::SORT_RANDOM));
            $pagination->setItemCount($category->getProducts()->count());
        }

        // Return everything
        $this->output(
            Response::HTTP_OK,
            [
                'products' => $this->getProductsHTML($products),
                'pagination' => $pagination->render(),
            ]
        );
    }

    /**
     * Return an array of products in HTML
     *
     * @param array $products
     *
     * @return array
     */
    private function getProductsHTML(array $products): array
    {
        $elements = [];
        $template = $this->getContainer()->get('templating');

        foreach ($products as $product) {
            $template->assign('product', $product);
            $elements[] = $template->getContent('Catalog/Layout/Templates/ProductItem.html.twig');
        }

        return $elements;
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

    /**
     * Get the catalog repository
     *
     * @return CategoryRepository
     */
    private function getCatalogRepository(): CategoryRepository
    {
        return $this->get('catalog.repository.category');
    }
}

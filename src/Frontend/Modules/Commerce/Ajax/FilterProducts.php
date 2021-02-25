<?php

namespace Frontend\Modules\Commerce\Ajax;

use Backend\Modules\Commerce\Domain\Category\CategoryRepository;
use Backend\Modules\Commerce\Domain\Category\Exception\CategoryNotFound;
use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\Product\ProductRepository;
use Frontend\Core\Engine\Base\AjaxAction as FrontendBaseAJAXAction;
use Frontend\Core\Language\Locale;
use Frontend\Modules\Commerce\Engine\Pagination;
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
        $categoryRepository = $this->getCommerceRepository();
        $locale = Locale::frontendLanguage();
        $itemsPerPage = $this->get('fork.settings')->get('Commerce', 'overview_num_items', 10);
        $currentPage = $this->getRequest()->get('page', 1);
        $productOffset = ($currentPage - 1) * $itemsPerPage;
        $sortOrder = $this->getRequest()->get('sort', Product::SORT_RANDOM);

        // Category or search term must be set
        if (!$this->getRequest()->request->has('category') && !$this->getRequest()->request->has('searchTerm')) {
            $this->output(Response::HTTP_NOT_FOUND);
            return;
        }

        // Build pagination
        $pagination = new Pagination();
        $pagination->setCurrentPage($currentPage);
        $pagination->setItemsPerPage($itemsPerPage);

        // Get the category
        if ($this->getRequest()->request->has('category') && !$this->getRequest()->request->has('searchTerm')) {
            try {
                $category = $categoryRepository->findOneByIdAndLocale($this->getRequest()->get('category'), $locale);
                $pagination->setBaseUrl($category->getUrl());

                // Filter the products
                if ($filters = $this->getRequest()->get('filters')) {
                    $products = $productRepository->filterProducts(
                        $filters,
                        $category,
                        $itemsPerPage,
                        $productOffset,
                        $sortOrder
                    );

                    $pagination->setItemCount($productRepository->filterProductsCount($filters, $category));
                } else {
                    $products = $productRepository->findLimitedByCategory(
                        $category,
                        $itemsPerPage,
                        $productOffset,
                        $sortOrder
                    );

                    $pagination->setItemCount($category->getActiveProducts()->count());
                }
            } catch (CategoryNotFound $e) {
                $this->output(Response::HTTP_NOT_FOUND);
                return;
            }
        } else { // Search on search term
            if ($filters = $this->getRequest()->get('filters')) {
                $products = $productRepository->filterSearchedProducts(
                    $this->getRequest()->request->get('searchTerm'),
                    $filters,
                    $itemsPerPage,
                    $productOffset,
                    $sortOrder
                );

                $pagination->setItemCount(
                    $productRepository->filterSearchedProductsCount(
                        $this->getRequest()->request->get('searchTerm'),
                        $filters
                    )
                );
            } else {
                $products = $productRepository->searchProductsLimited(
                    $this->getRequest()->request->get('searchTerm'),
                    $itemsPerPage,
                    $productOffset,
                    $sortOrder
                );

                $pagination->setItemCount(
                    $productRepository->getSearchProductCount(
                        $this->getRequest()->request->get('searchTerm'),
                        Locale::frontendLanguage()
                    )
                );
            }
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
            $elements[] = $template->getContent('Commerce/Layout/Templates/ProductItem.html.twig');
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
        return $this->get('commerce.repository.product');
    }

    /**
     * Get the commerce repository
     *
     * @return CategoryRepository
     */
    private function getCommerceRepository(): CategoryRepository
    {
        return $this->get('commerce.repository.category');
    }
}

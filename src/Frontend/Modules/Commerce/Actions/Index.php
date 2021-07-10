<?php

namespace Frontend\Modules\Commerce\Actions;

use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\Cart\CartRepository;
use Backend\Modules\Commerce\Domain\Cart\CartValueRepository;
use Backend\Modules\Commerce\Domain\Category\Category;
use Backend\Modules\Commerce\Domain\Category\CategoryRepository;
use Backend\Modules\Commerce\Domain\Product\AddToCartDataTransferObject;
use Backend\Modules\Commerce\Domain\Product\AddToCartType;
use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\Product\ProductRepository;
use Backend\Modules\Commerce\Domain\Specification\SpecificationRepository;
use Common\Exception\RedirectException;
use Frontend\Core\Engine\Base\Block as FrontendBaseBlock;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Core\Language\Language;
use Frontend\Core\Language\Locale;
use Frontend\Modules\Commerce\Engine\Pagination;
use Frontend\Modules\Commerce\Engine\ProductSorting;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * This is the overview-action.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Index extends FrontendBaseBlock
{
    public function execute(): void
    {
        parent::execute();

        $categoryRepository = $this->getCategoryRepository();
        $productRepository = $this->getProductRepository();

        $parameters = $this->url->getParameters(false);
        $parameterCount = count($parameters);

        if ($parameterCount >= 3) { // Parent category, category and product
            if ($product = $productRepository->findByCategoryAndUrl(
                Locale::frontendLanguage(),
                $parameters[1],
                $parameters[2]
            )) {
                $this->parseProduct($product);
            } else {
                $this->redirect(FrontendNavigation::getUrl(404));
            }
        } elseif ($parameterCount === 2) {
            if ($product = $productRepository->findByCategoryAndUrl(
                Locale::frontendLanguage(),
                $parameters[0],
                $parameters[1]
            )) {
                $this->parseProduct($product);
            } elseif ($category = $categoryRepository->findByLocaleAndUrl(Locale::frontendLanguage(), $parameters[1])) {
                $this->parseCategory($category);
            } else {
                $this->redirect(FrontendNavigation::getUrl(404));
            }
        } elseif (
            $parameterCount === 1 && (
                $category = $categoryRepository->findByLocaleAndUrl(Locale::frontendLanguage(), $parameters[0])
            )
        ) {
            // Category-view
            $this->parseCategory($category);
        } elseif ($parameterCount === 0) {
            // Overview
            $this->parseOverview();
        } else {
            $this->redirect(FrontendNavigation::getUrl(404));
        }
    }

    private function parseOverview(): void
    {
        $this->loadTemplate();

        // add css
        $this->addCSS('Commerce.css');

        $this->template->assign('categories', $this->getCategoryRepository()->findParents(Locale::frontendLanguage()));
        $this->template->assign('categoriesBaseUrl', FrontendNavigation::getURLForBlock('Commerce'));
    }

    /**
     * Parse products overview in a category.
     */
    private function parseCategory(Category $category): void
    {
        // Set some default variables
        $currentPage = $this->url->getParameter(Language::lbl('Page'), 'int', 1);
        $itemsPerPage = $this->get('fork.settings')->get('Commerce', 'overview_num_items', 10);
        $filtersShowMoreCount = $this->get('fork.settings')->get('Commerce', 'filters_show_more_num_items', 5);
        $productRepository = $this->getProductRepository();
        $specificationRepository = $this->getSpecificationRepository();
        $productOffset = ($currentPage - 1) * $itemsPerPage;
        $baseUrl = '/'.implode('/', array_merge($this->url->getPages(), $this->url->getParameters(false)));

        // Set page defaults
        $this->loadTemplate('Commerce/Layout/Templates/Category.html.twig');
        if ($category->getParent()) {
            $this->categoryPageTitles($category->getParent());
        }
        $this->setMeta($category->getMeta());

        // add css
        $this->addCSS('Commerce.css');

        // Add JS
        $this->addJSData('filterUrl', $baseUrl);
        $this->addJSData('category', $category->getId());
        $this->addJS('Filter.js');

        // Add categories to breadcrumbs
        $this->categoryToBreadcrumb($category);

        // Define the sort orders
        $sortOrders = ProductSorting::getAll();
        $currentSortOrder = $this->getRequest()->get('sort', Product::SORT_STANDARD);
        if (array_key_exists($currentSortOrder, $sortOrders)) {
            $sortOrders[$currentSortOrder]['selected'] = true;
        }

        // Get the filters for current category
        $filters = $specificationRepository->findFiltersByCategory($category);

        // Fetch the products
        if ($productFilters = $this->getProductFilters()) {
            $products = $productRepository->filterProducts(
                $productFilters,
                $category,
                $itemsPerPage,
                $productOffset,
                $currentSortOrder
            );

            $itemCount = $productRepository->filterProductsCount($productFilters, $category);
            $this->pagination = $this->buildPaginationConfig($itemCount);
        } else {
            $products = $productRepository->findLimitedByCategory(
                $category,
                $itemsPerPage,
                $productOffset,
                $currentSortOrder
            );
            $itemCount = $category->getActiveProducts()->count();
            $this->pagination = $this->buildPaginationConfig($itemCount);
        }

        // Assign to our template
        $this->template->assign('categories', $this->getCategoryRepository()->findParents(Locale::frontendLanguage()));
        $this->template->assign('category', $category);
        $this->template->assign('products', $products);
        $this->template->assign('filters', $filters);
        $this->template->assign('sortOrders', $sortOrders);
        $this->template->assign('filtersShowMoreCount', $filtersShowMoreCount);
        $this->parsePagination();
    }

    /**
     * Parse product.
     */
    private function parseProduct(Product $product)
    {
        // Set page defaults
        $this->loadTemplate('Commerce/Layout/Templates/Product.html.twig');

        // Add category titles to the header
        $this->categoryPageTitles($product->getCategory());

        $this->setMeta($product->getMeta());

        // Add the breadcrumbs
        $this->categoryToBreadcrumb($product->getCategory());
        $this->breadcrumb->addElement($product->getTitle(), $product->getUrl());

        // Add js
        $this->addJS('jquery.sudoSlider.min.js');
        $this->addJS('jquery.fancybox.min.js');
        $this->addJS('owl.carousel.min.js');
        $this->addJs('Product.js');
        $this->addJS('EnhancedEcommerce.js');

        // Add js data
        $this->addJSData('isProductDetail', true);

        // Add css
        $this->addCSS('jquery.fancybox.min.css');
        $this->addCSS('owl.carousel.min.css');
        $this->addCSS('Commerce.css');

        // build the form
        $form = $this->getForm($product);

        // build the images widget
        $images = $this->get('media_library.helper.frontend')->parseWidget(
            'ProductImages',
            $product->getImages()->getId(),
            ucfirst(Language::lbl('Images')),
            $this->getModule()
        );

        $downloads = null;
        if ($product->getDownloads()->hasConnectedItems()) {
            $downloads = $this->get('media_library.helper.frontend')->parseWidget(
                'ProductDownloads',
                $product->getDownloads()->getId(),
                sprintf(ucfirst(Language::lbl('FilesFor')), $product->getTitle()),
                'Commerce'
            );
        }

        $this->template->assign('images', $images);
        $this->template->assign('downloads', $downloads);
        $this->template->assign('specifications', $this->getSpecificationRepository()->findByProduct($product));
        $this->template->assign('product', $product);
        $this->template->assign('form', $form->createView());
        $this->template->assign(
            'siteTitle',
            $this->get('fork.settings')->get('Core', 'site_title_'.Locale::frontendLanguage())
        );
    }

    private function categoryToBreadcrumb(Category $category): void
    {
        if ($category->getParent()) {
            $this->categoryToBreadcrumb($category->getParent());
        }

        $this->breadcrumb->addElement($category->getTitle(), $category->getUrl());
    }

    private function categoryPageTitles(Category $category): void
    {
        if ($category->getParent()) {
            $this->categoryPageTitles($category->getParent());
        }

        $this->header->setPageTitle($category->getTitle());
    }

    private function getForm(Product $product)
    {
        $cartValue = null;
        $cart = $this->getActiveCart();
        $cartId = $this->getRequest()->query->getInt('cart_id');

        if ($cart && $cartId) {
            $cartValue = $this->getCartValueRepository()->getByCartAndId($cart, $cartId);
        }

        $dataTransferObject = new AddToCartDataTransferObject($product, $cartValue);
        if (!$cartValue && $this->getRequest()->query->has('width') && $this->getRequest()->query->has('height')) {
            $dataTransferObject->width = $this->getRequest()->query->get('width');
            $dataTransferObject->height = $this->getRequest()->query->get('height');
        }

        $this->addJSData('cartId', $cartId);

        return $this->createForm(
            AddToCartType::class,
            $dataTransferObject,
            [
                'product' => $product,
            ]
        );
    }

    /**
     * Get the active cart from the session.
     *
     * @return Cart
     */
    private function getActiveCart(): ?Cart
    {
        if (!$cartHash = $this->get('fork.cookie')->get('cart_hash')) {
            return null;
        }

        $cartRepository = $this->getCartRepository();

        return $cartRepository->findBySessionId($cartHash, $this->getRequest()->getClientIp());
    }

    private function getCategoryRepository(): CategoryRepository
    {
        return $this->get('commerce.repository.category');
    }

    private function getProductRepository(): ProductRepository
    {
        return $this->get('commerce.repository.product');
    }

    private function getSpecificationRepository(): SpecificationRepository
    {
        return $this->get('commerce.repository.specification');
    }

    /**
     * Get an array with the filters which could be used.
     */
    private function getProductFilters(): array
    {
        $filters = [];

        foreach ($this->getRequest()->query->all() as $key => $value) {
            $filters[$key] = explode(',', $value);
        }

        return $filters;
    }

    private function buildPaginationConfig(int $numberOfItems): array
    {
        $requestedPage = $this->url->getParameter('page', 'int', 1);

        $limit = $this->get('fork.settings')->get($this->getModule(), 'overview_num_items', 10);
        $numberOfPages = (int) ceil($numberOfItems / $limit);

        if ($numberOfPages === 0) {
            $numberOfPages = 1;
        }

        // Check if the page exists
        if ($requestedPage > $numberOfPages || $requestedPage < 1) {
            throw new NotFoundHttpException();
        }

        return [
            'url' => '/'.implode('/', array_merge($this->url->getPages(), $this->url->getParameters(false))),
            'limit' => $limit,
            'offset' => ($requestedPage * $limit) - $limit,
            'requested_page' => $requestedPage,
            'num_items' => $numberOfItems,
            'num_pages' => $numberOfPages,
        ];
    }

    /**
     * Get the cart repository.
     */
    private function getCartRepository(): CartRepository
    {
        return $this->get('commerce.repository.cart');
    }

    /**
     * Get the cart value repository.
     */
    private function getCartValueRepository(): CartValueRepository
    {
        return $this->get('commerce.repository.cart_value');
    }
}

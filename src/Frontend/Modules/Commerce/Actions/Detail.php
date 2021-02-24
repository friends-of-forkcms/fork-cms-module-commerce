<?php

namespace Frontend\Modules\Commerce\Actions;

use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\Product\ProductRepository;
use Frontend\Core\Engine\Base\Block as FrontendBaseBlock;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Core\Language\Locale;

/**
 * This is the detail-action, it will display a product.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Detail extends FrontendBaseBlock
{
    /**
     * The information about a product.
     *
     * @var Product
     */
    private $product;

    /**
     * The specifications of a product.
     *
     * @var array
     */
    private $specifications;

    /**
     * The tags of a product.
     *
     * @var array
     */
    private $tags;

    /**
     * The comments of a product.
     *
     * @var array
     */
    private $comments;

    /**
     * Module settings.
     *
     * @var array
     */
    private $settings;

    /**
     * The related products.
     *
     * @var array
     */
    private $relatedProducts;

    /**
     * Videos from a product.
     *
     * @var array
     */
    private $videos;

    /**
     * Files from a product.
     *
     * @var array
     */
    private $files;

    /**
     * Images from a product.
     *
     * @var array
     */
    private $images;

    /**
     * Brand from a product.
     *
     * @var array
     */
    private $brand;

    /**
     * Execute the action.
     */
    public function execute(): void
    {
        parent::execute();

        $this->loadTemplate();
        $this->getData();

        $this->parse();
    }

    /**
     * Get the data.
     */
    private function getData()
    {
        // validate incoming parameters
        if ($this->url->getParameter(1) === null) {
            $this->redirect(FrontendNavigation::getURL(404));
        }

        /**
         * @var ProductRepository
         */
        $productRepository = $this->get('commerce.repository.product');

        if (!$this->product = $productRepository->findByLocaleAndUrl(
            Locale::frontendLanguage(),
            $this->url->getParameter(1)
        )) {
            $this->redirect(FrontendNavigation::getURL(404));
        }
    }

    /**
     * Parse the page.
     */
    protected function parse()
    {
        // add css
        $this->header->addCSS('/src/Frontend/Modules/'.$this->getModule().'/Layout/Css/commerce.css');

        // add noty js
        $this->header->addJS('/src/Frontend/Modules/'.$this->getModule().'/Js/noty/packaged/jquery.noty.packaged.min.js');

        // add into breadcrumb
        $this->breadcrumb->addElement($this->product->getTitle());

        // show title linked with the meta title
        $this->template->assign('title', $this->product->getTitle());

        // set meta
        $this->setMeta($this->product->getMeta());

        // build the images widget
        $this->template->assign(
            'images',
            $this->get('media_library.helper.frontend')->parseWidget(
                'ProductImages',
                $this->product->getImages()->getId(),
                'MyCustomOptionalTitle',
                'Commerce'
            )
        );

        // assign item information
        $this->template->assign('product', $this->product);
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\Product;

use JeroenDesloovere\SitemapBundle\Item\ChangeFrequency;
use JeroenDesloovere\SitemapBundle\Provider\SitemapProvider;
use JeroenDesloovere\SitemapBundle\Provider\SitemapProviderInterface;

class ProductSitemapProvider extends SitemapProvider implements SitemapProviderInterface
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;

        parent::__construct('CommerceProducts');
    }

    public function createItems(): void
    {
        $products = $this->productRepository->findActive();
        foreach ($products as $product) {
            $this->createItem($product->getUrl(), $product->getEditedOn(), ChangeFrequency::daily());
        }
    }
}

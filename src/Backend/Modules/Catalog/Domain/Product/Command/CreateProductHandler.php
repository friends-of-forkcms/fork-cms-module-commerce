<?php

namespace Backend\Modules\Catalog\Domain\Product\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\Product\ProductRepository;
use Common\ModuleExtraType;

final class CreateProductHandler
{
    /** @var ProductRepository */
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function handle(CreateProduct $createProduct): void
    {
        $createProduct->extraId = $this->getNewExtraId();
        $createProduct->sequence = $this->productRepository->getNextSequence(
            $createProduct->locale,
            $createProduct->category
        );

        /**
         * @var Product $product
         *
         * create our product
         */
        $product = Product::fromDataTransferObject($createProduct);

        // set the new product entity
        foreach ($createProduct->specification_values as $specification_value) {
            $specification_value->setProduct($product);
        }

        // set the new product entity
        foreach ($createProduct->specials as $special) {
            $special->setProduct($product);
        }

        foreach ($createProduct->up_sell_products as $up_sell_product) {
            $up_sell_product->setProduct($product);
        }

        // add our new product to the relating products
        foreach ($createProduct->related_products as $related_product) {
            $related_product->addRelatedProduct($product);
        }

        // add our product to the database
        $this->productRepository->add($product);

        $createProduct->setProductEntity($product);
    }

    private function getNewExtraId(): int
    {
        return Model::insertExtra(
            ModuleExtraType::widget(),
            'Catalog',
            'Product'
        );
    }
}

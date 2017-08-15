<?php

namespace Backend\Modules\Catalog\Domain\Product\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\Product\ProductRepository;


final class UpdateHandler
{
    /** @var ProductRepository */
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function handle(Update $updateProduct): void
    {
        $product = Product::fromDataTransferObject($updateProduct);

        // set the new product entity
        foreach ($updateProduct->specification_values as $specification_value) {
            $specification_value->setProduct($product);
        }

        // remove the specification values
        $entityManager = Model::get('doctrine.orm.entity_manager');
        foreach ($updateProduct->remove_specification_values as $specification_value) {
            $entityManager->remove($specification_value);
        }

        // set the new product entity
        foreach ($updateProduct->specials as $special) {
            $special->setProduct($product);
        }

        // remove specials
        $entityManager = Model::get('doctrine.orm.entity_manager');
        foreach ($updateProduct->remove_specials as $special) {
            $entityManager->remove($special);
        }

        // first update all the relating product
        foreach ($updateProduct->related_products as $related_product) {
            // cleanup current data
            $related_product->removeRelatedProduct($product);

            // add new related products
            $related_product->addRelatedProduct($product);
        }

        // second remove all the other related products
        foreach ($updateProduct->remove_related_products as $related_product) {
            $related_product->removeRelatedProduct($product);
        }

        $entityManager->flush();


        // store the product
        $this->productRepository->add($product);

        $updateProduct->setProductEntity($product);
    }
}

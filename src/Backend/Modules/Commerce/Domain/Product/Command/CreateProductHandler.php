<?php

namespace Backend\Modules\Commerce\Domain\Product\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\Product\ProductRepository;
use Backend\Modules\Search\Engine\Model as BackendSearchModel;
use Common\ModuleExtraType;

final class CreateProductHandler
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function handle(CreateProduct $createProduct): void
    {
        $createProduct->extraId = $this->getNewExtraId();
        $product = Product::fromDataTransferObject($createProduct);

        // save the dimensions
        foreach ($createProduct->dimensions as $dimension) {
            $dimension->setProduct($product);
        }

        // save the dimension notifications
        foreach ($createProduct->dimension_notifications as $dimension_notification) {
            $dimension_notification->setProduct($product);
        }

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

        // Flush because we need to reference the product ID (switch to uuid?)
        $entityManager = Model::get('doctrine.orm.entity_manager');
        $entityManager->flush();

        // add search index
        if (!$product->isHidden()) {
            BackendSearchModel::saveIndex(
                'Commerce',
                $product->getId(),
                array_filter([
                    'title' => $product->getTitle(),
                    'text' => $product->getText(),
                    'sku' => $product->getSku(),
                    'ean13' => $product->getEan13(),
                    'isbn' => $product->getIsbn(),
                    'brand' => $product->getBrand() ? $product->getBrand()->getTitle() : null,
                ])
            );
        }
    }

    private function getNewExtraId(): int
    {
        return Model::insertExtra(
            ModuleExtraType::widget(),
            'Commerce',
            'Product'
        );
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\Product\Command;

use Backend\Modules\Commerce\Domain\Product\ProductRepository;

final class DeleteProductHandler
{
    /** @var ProductRepository */
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function handle(DeleteProduct $deleteProduct): void
    {
        $this->productRepository->removeByIdAndLocale(
            $deleteProduct->product->getId(),
            $deleteProduct->product->getLocale()
        );
    }
}

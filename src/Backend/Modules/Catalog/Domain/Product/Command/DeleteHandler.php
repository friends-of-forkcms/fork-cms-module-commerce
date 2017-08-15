<?php

namespace Backend\Modules\Catalog\Domain\Product\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Catalog\Domain\Product\ProductRepository;

final class DeleteHandler
{
    /** @var ProductRepository */
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function handle(Delete $deleteProduct): void
    {
        $this->productRepository->removeByIdAndLocale(
            $deleteProduct->product->getId(),
            $deleteProduct->product->getLocale()
        );
    }
}

<?php

namespace Backend\Modules\Catalog\Domain\ProductOption\Command;

use Backend\Modules\Catalog\Domain\ProductOption\ProductOptionRepository;

final class DeleteProductOptionHandler
{
    /** @var ProductOptionRepository */
    private $productOptionRepository;

    public function __construct(ProductOptionRepository $productOptionRepository)
    {
        $this->productOptionRepository = $productOptionRepository;
    }

    public function handle(DeleteProductOption $deleteProductOption): void
    {
        $this->productOptionRepository->removeById($deleteProductOption->productOption->getId());
    }
}

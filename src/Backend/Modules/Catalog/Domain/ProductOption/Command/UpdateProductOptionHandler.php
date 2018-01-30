<?php

namespace Backend\Modules\Catalog\Domain\ProductOption\Command;

use Backend\Modules\Catalog\Domain\ProductOption\ProductOption;
use Backend\Modules\Catalog\Domain\ProductOption\ProductOptionRepository;

final class UpdateProductOptionHandler
{
    /** @var ProductOptionRepository */
    private $productOptionRepository;

    public function __construct(ProductOptionRepository $productOptionRepository)
    {
        $this->productOptionRepository = $productOptionRepository;
    }

    public function handle(UpdateProductOption $updateProductOption): void
    {
        $productOption = ProductOption::fromDataTransferObject($updateProductOption);

        // store the product
        $this->productOptionRepository->add($productOption);

        $updateProductOption->setProductOptionEntity($productOption);
    }
}

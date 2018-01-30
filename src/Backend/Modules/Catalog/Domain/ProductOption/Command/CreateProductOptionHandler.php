<?php

namespace Backend\Modules\Catalog\Domain\ProductOption\Command;

use Backend\Modules\Catalog\Domain\ProductOption\ProductOption;
use Backend\Modules\Catalog\Domain\ProductOption\ProductOptionRepository;

final class CreateProductOptionHandler
{
    /** @var ProductOptionRepository */
    private $productOptionRepository;

    public function __construct(ProductOptionRepository $productRepository)
    {
        $this->productOptionRepository = $productRepository;
    }

    public function handle(CreateProductOption $createProductOption): void
    {
        $createProductOption->sequence = $this->productOptionRepository->getNextSequence($createProductOption->product);
        $productOption = ProductOption::fromDataTransferObject($createProductOption);

        // add our product to the database
        $this->productOptionRepository->add($productOption);

        $createProductOption->setProductOptionEntity($productOption);
    }
}

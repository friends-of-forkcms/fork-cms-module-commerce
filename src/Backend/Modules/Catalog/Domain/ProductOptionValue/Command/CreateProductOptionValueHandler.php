<?php

namespace Backend\Modules\Catalog\Domain\ProductOptionValue\Command;

use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue;
use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValueRepository;

final class CreateProductOptionValueHandler
{
    /** @var ProductOptionValueRepository */
    private $productOptionValueRepository;

    public function __construct(ProductOptionValueRepository $productOptionValueRepository)
    {
        $this->productOptionValueRepository = $productOptionValueRepository;
    }

    public function handle(CreateProductOptionValue $createProductOptionValue): void
    {
        $createProductOptionValue->sequence = $this->productOptionValueRepository->getNextSequence(
            $createProductOptionValue->productOption
        );

        $productOptionValue = ProductOptionValue::fromDataTransferObject($createProductOptionValue);
        $this->productOptionValueRepository->add($productOptionValue);

        $createProductOptionValue->setProductOptionValueEntity($productOptionValue);
    }
}

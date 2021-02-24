<?php

namespace Backend\Modules\Commerce\Domain\ProductOptionValue\Command;

use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValueRepository;

final class CreateProductOptionValueHandler
{
    private ProductOptionValueRepository $productOptionValueRepository;

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

<?php

namespace Backend\Modules\Commerce\Domain\ProductOption\Command;

use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOptionRepository;

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
        if ($createProductOption->custom_value_price === null) {
            $createProductOption->custom_value_price = 0.00;
        }

        $productOption = ProductOption::fromDataTransferObject($createProductOption);

        // save the dimension notifications
        foreach ($createProductOption->dimension_notifications as $dimension_notification) {
            $dimension_notification->setProductOption($productOption);
        }

        // add our product to the database
        $this->productOptionRepository->add($productOption);

        $createProductOption->setProductOptionEntity($productOption);
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\ProductOption\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOptionRepository;

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
        if ($updateProductOption->custom_value_price === null) {
            $updateProductOption->custom_value_price = 0.00;
        }

        $productOption = ProductOption::fromDataTransferObject($updateProductOption);
        $entityManager = Model::get('doctrine.orm.entity_manager');

        // save the dimension notifications
        foreach ($updateProductOption->dimension_notifications as $dimension_notification) {
            $dimension_notification->setProductOption($productOption);
        }

        foreach ($updateProductOption->remove_dimension_notifications as $dimension_notification) {
            $entityManager->remove($dimension_notification);
        }

        $entityManager->flush();

        // store the product
        $this->productOptionRepository->add($productOption);

        $updateProductOption->setProductOptionEntity($productOption);
    }
}

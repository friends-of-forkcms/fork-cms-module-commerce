<?php

namespace Frontend\Modules\Commerce\Engine;

use Backend\Modules\Commerce\Domain\Product\Product;
use Frontend\Core\Language\Language;

/**
 * Class ProductSorting
 * @package Frontend\Modules\Commerce\Engine
 */
class ProductSorting
{
    public static function getAll(): array
    {
        return [
            Product::SORT_STANDARD => [
                'label' => Language::getLabel('SortRandom'),
                'selected' => false,
            ],
            Product::SORT_PRICE_ASC => [
                'label' => Language::getLabel('SortPriceLowToHigh'),
                'selected' => false,
            ],
            Product::SORT_PRICE_DESC => [
                'label' => Language::getLabel('SortPriceHighToLow'),
                'selected' => false,
            ],
            Product::SORT_CREATED_AT => [
                'label' => Language::getLabel('SortNewest'),
                'selected' => false,
            ],
        ];
    }
}

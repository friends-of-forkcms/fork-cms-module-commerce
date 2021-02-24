<?php

namespace Frontend\Modules\Commerce\Ajax;

use Backend\Modules\Commerce\Domain\Product\AddToCartDataTransferObject;
use Backend\Modules\Commerce\Domain\Product\AddToCartType;
use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\Product\ProductRepository;
use Backend\Modules\Commerce\Domain\ProductDimension\ProductDimensionRepository;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;
use Frontend\Core\Engine\Base\AjaxAction as FrontendBaseAJAXAction;
use Symfony\Component\Form\Form;

class DimensionCalculator extends FrontendBaseAJAXAction
{
    /**
     * @var AddToCartDataTransferObject
     */
    protected $data;

    /**
     * @var int
     */
    private $width = 0;

    /**
     * @var int
     */
    private $height = 0;

    /**
     * @var float
     */
    private $basePrice = 0;

    /**
     * @var int
     */
    private $total = 0;

    /**
     * @param $basePrice
     */
    protected function setBasePrice(float $basePrice): void
    {
        $this->basePrice = $basePrice;
    }

    /**
     * @return int
     */
    protected function getBasePrice(): float
    {
        return $this->basePrice;
    }

    /**
     * @param $total
     */
    protected function addTotalPrice(float $total): void
    {
        $this->total += $total;
    }

    protected function getTotalPrice(): float
    {
        return $this->total;
    }

    protected function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     */
    protected function addWidth(?int $width): void
    {
        $this->width += $width;
    }

    protected function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    protected function addHeight(?int $height): void
    {
        $this->height += $height;
    }

    /**
     * Get the product form.
     */
    protected function getForm(Product $product): Form
    {
        return $this->get('form.factory')->create(
            AddToCartType::class,
            new AddToCartDataTransferObject($product),
            [
                'product' => $product,
            ]
        );
    }

    /**
     * @param ProductOption[] $productOptions
     */
    protected function parseProductOptionsDimension($productOptions): void
    {
        foreach ($productOptions as $option) {
            $propertyName = 'option_'.$option->getId();
            $propertyNameCustomValue = $propertyName.'_custom_value';

            if (!property_exists($this->data, $propertyName) || $option->isTextType() || $option->isColorType()) {
                continue;
            }

            /**
             * @var ProductOptionValue $optionValue
             */
            $optionValue = $this->data->{$propertyName};

            if ($option->isCustomValueAllowed() && $this->data->{$propertyNameCustomValue}) {
                // @TODO for now a select adds height with a custom value
                $this->addHeight((int) $this->data->{$propertyNameCustomValue});
            }

            if ($optionValue) {
                if ($optionValue->getWidth() || $optionValue->getHeight()) {
                    $multiplier = $optionValue->isImpactTypeAdd() ? 1 : -1;
                    $this->addWidth($optionValue->getWidth() * $multiplier);
                    $this->addHeight($optionValue->getHeight() * $multiplier);
                }

                $this->parseProductOptionsDimension($optionValue->getProductOptions());
            }
        }
    }

    /**
     * Get the product dimension repository.
     */
    protected function getProductDimensionRepository(): ProductDimensionRepository
    {
        return $this->get('commerce.repository.product_dimension');
    }

    /**
     * Get the product repository.
     */
    protected function getProductRepository(): ProductRepository
    {
        return $this->get('commerce.repository.product');
    }
}

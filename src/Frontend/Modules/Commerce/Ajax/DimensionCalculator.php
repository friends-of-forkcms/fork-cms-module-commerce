<?php

namespace Frontend\Modules\Commerce\Ajax;

use Backend\Modules\Commerce\Domain\Product\AddToCartDataTransferObject;
use Backend\Modules\Commerce\Domain\Product\AddToCartType;
use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\Product\ProductRepository;
use Backend\Modules\Commerce\Domain\ProductDimension\ProductDimensionRepository;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;
use Doctrine\Common\Collections\Collection;
use Frontend\Core\Engine\Base\AjaxAction as FrontendBaseAJAXAction;
use Money\Money;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpKernel\KernelInterface;

class DimensionCalculator extends FrontendBaseAJAXAction
{
    protected AddToCartDataTransferObject $data;

    private int $width = 0;
    private int $height = 0;
    private Money $basePrice;
    private Money $total;

    public function __construct(KernelInterface $kernel, string $action, string $module)
    {
        parent::__construct($kernel, $action, $module);
        $this->basePrice = Money::EUR(0);
        $this->total = Money::EUR(0);
    }

    protected function setBasePrice(Money $basePrice): void
    {
        $this->basePrice = $basePrice;
    }

    protected function getBasePrice(): Money
    {
        return $this->basePrice;
    }

    protected function addTotalPrice(Money $total): void
    {
        $this->total = $this->total->add($total);
    }

    protected function getTotalPrice(): Money
    {
        return $this->total;
    }

    protected function getWidth(): int
    {
        return $this->width;
    }

    protected function addWidth(int $width): void
    {
        $this->width += $width;
    }

    protected function getHeight(): int
    {
        return $this->height;
    }

    protected function addHeight(int $height): void
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
     * @param Collection<int, ProductOption>|ProductOption[] $productOptions
     */
    protected function parseProductOptionsDimension(Collection $productOptions): void
    {
        foreach ($productOptions as $option) {
            $propertyName = 'option_'.$option->getId();
            $propertyNameCustomValue = $propertyName.'_custom_value';

            if (!property_exists($this->data, $propertyName) || $option->isTextType() || $option->isColorType()) {
                continue;
            }

            /** @var ProductOptionValue $optionValue */
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

    protected function getProductDimensionRepository(): ProductDimensionRepository
    {
        return $this->get('commerce.repository.product_dimension');
    }

    protected function getProductRepository(): ProductRepository
    {
        return $this->get('commerce.repository.product');
    }
}

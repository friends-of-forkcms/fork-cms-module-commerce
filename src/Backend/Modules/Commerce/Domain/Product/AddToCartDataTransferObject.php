<?php

namespace Backend\Modules\Commerce\Domain\Product;

use Backend\Modules\Commerce\Domain\Cart\CartValue;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Doctrine\Common\Collections\Collection;
use Frontend\Core\Language\Language;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AddToCartDataTransferObject
{
    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public int $id;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public int $amount;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired", groups={"Dimensions"})
     */
    public ?float $width = 0.00;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired", groups={"Dimensions"})
     */
    public ?float $height = 0.00;
    public bool $quote;
    public bool $overwrite;

    /**
     * @var Product[]
     */
    public array $up_sell;
    public Product $product;
    public CartValue $cartValueEntity;

    public function __construct(Product $product, ?CartValue $cartValue = null)
    {
        $this->id = $product->getId();
        $this->amount = $product->getOrderQuantity();
        $this->product = $product;
        $this->quote = false;
        $this->overwrite = false;

        $prefix = 'option_';
        $this->loadProductOptions($product->getProductOptions(), $prefix);

        if ($cartValue) {
            $this->amount = $cartValue->getQuantity();
            $this->width = $cartValue->getWidth();
            $this->height = $cartValue->getHeight();

            foreach ($cartValue->getCartValueOptions() as $cartValueOption) {
                $productOption = $cartValueOption->getProductOption();

                $key = $prefix.$productOption->getId();
                $customValueKey = $key.'_custom_value';

                if (!property_exists($this, $key)) {
                    return;
                }

                if ($productOption->isCustomValueAllowed() && !$cartValueOption->getProductOptionValue()) {
                    $this->{$customValueKey} = $cartValueOption->getValue();
                } else {
                    $this->{$key} = $cartValueOption->getProductOptionValue();
                }
            }
        }
    }

    public function validate(ExecutionContextInterface $context, $payload): void
    {
        foreach ($this->product->getProductOptions() as $productOption) {
            $this->validateProductOption($productOption, $context);
        }
    }

    private function validateProductOption(ProductOption $productOption, ExecutionContextInterface $context): void
    {
        $fieldName = 'option_'.$productOption->getId();
        $customValueFieldName = $fieldName.'_custom_value';
        $valid = true;

        if ($productOption->isRequired()) {
            $valid = $this->buildRequiredViolation(
                $productOption,
                $context,
                $fieldName,
                $customValueFieldName
            );
        }

        if ($valid) {
            foreach ($productOption->getProductOptionValues() as $productOptionValue) {
                if ($this->$fieldName && $this->$fieldName->getId() === $productOptionValue->getId()) {
                    foreach ($productOptionValue->getProductOptions() as $subProductOption) {
                        $this->validateProductOption($subProductOption, $context);
                    }
                }
            }
        }
    }

    private function buildRequiredViolation(
        ProductOption $productOption,
        ExecutionContextInterface $context,
        string $fieldName,
        string $customValueFieldName
    ): bool {
        $valid = true;

        if ($productOption->isCustomValueAllowed()) {
            if (!$this->$fieldName && !$this->$customValueFieldName) {
                $context->buildViolation(ucfirst(Language::err('FieldIsRequired')))
                    ->atPath($fieldName)
                    ->addViolation();

                $valid = false;
            }
        } elseif (!$this->$fieldName) {
            $context->buildViolation(ucfirst(Language::err('FieldIsRequired')))
                ->atPath($fieldName)
                ->addViolation();

            $valid = false;
        }

        return $valid;
    }

    /**
     * @param Collection|ProductOption[] $productOptions
     */
    private function loadProductOptions(Collection $productOptions, string $prefix): void
    {
        foreach ($productOptions as $productOption) {
            $this->{$prefix.$productOption->getId()} = $productOption->getDefaultProductOptionValue();

            if ($productOption->isCustomValueAllowed() || $productOption->isBetweenType()) {
                $this->{$prefix.$productOption->getId().'_custom_value'} = null;
            }

            foreach ($productOption->getProductOptionValues() as $productOptionValue) {
                $this->loadProductOptions($productOptionValue->getProductOptions(), $prefix);
            }
        }
    }
}

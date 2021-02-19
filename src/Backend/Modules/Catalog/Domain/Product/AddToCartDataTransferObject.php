<?php

namespace Backend\Modules\Catalog\Domain\Product;

use Backend\Modules\Catalog\Domain\Cart\CartValue;
use Backend\Modules\Catalog\Domain\ProductOption\ProductOption;
use Frontend\Core\Language\Language;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

class AddToCartDataTransferObject
{
    /**
     * @var integer
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $id;

    /**
     * @var integer
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $amount;

    /**
     * @var integer
     *
     * @Assert\NotBlank(message="err.FieldIsRequired", groups={"Dimensions"})
     */
    public $width = 0.00;

    /**
     * @var integer
     *
     * @Assert\NotBlank(message="err.FieldIsRequired", groups={"Dimensions"})
     */
    public $height = 0.00;

    /**
     * @var boolean
     */
    public $quote;

    /**
     * @var boolean
     */
    public $overwrite;

    /**
     * @var Product[]
     */
    public $up_sell;

    /**
     * @var Product
     */
    public $product;

    /**
     * @var CartValue
     */
    public $cartValueEntity;

    /**
     * AddToCartDataTransferObject constructor.
     *
     * @param Product $product
     * @param CartValue $cartValue
     */
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

                $key = $prefix . $productOption->getId();
                $customValueKey = $key . '_custom_value';

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

    /**
     * @Assert\Callback
     * @param ExecutionContextInterface $context
     * @param $payload
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        foreach ($this->product->getProductOptions() as $productOption) {
            $this->validateProductOption($productOption, $context);
        }
    }

    private function validateProductOption(ProductOption $productOption, ExecutionContextInterface $context)
    {
        $fieldName = 'option_' . $productOption->getId();
        $customValueFieldName = $fieldName . '_custom_value';
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
                if ($this->$fieldName && $this->$fieldName->getId() == $productOptionValue->getId()) {
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
    ): bool
    {
        $valid = true;

        if ($productOption->isCustomValueAllowed()) {
            if (!$this->$fieldName && !$this->$customValueFieldName) {
                $context->buildViolation(ucfirst(Language::err('FieldIsRequired')))
                    ->atPath($fieldName)
                    ->addViolation();

                $valid = false;
            }
        } else {
            if (!$this->$fieldName) {
                $context->buildViolation(ucfirst(Language::err('FieldIsRequired')))
                    ->atPath($fieldName)
                    ->addViolation();

                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * @param ProductOption[] $productOptions
     * @param string $prefix
     */
    private function loadProductOptions($productOptions, string $prefix)
    {
        foreach ($productOptions as $productOption) {
            $this->{$prefix . $productOption->getId()} = $productOption->getDefaultProductOptionValue();

            if ($productOption->isCustomValueAllowed() || $productOption->isBetweenType()) {
                $this->{$prefix . $productOption->getId() . '_custom_value'} = null;
            }

            foreach ($productOption->getProductOptionValues() as $productOptionValue) {
                $this->loadProductOptions($productOptionValue->getProductOptions(), $prefix);
            }
        }
    }
}

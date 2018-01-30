<?php

namespace Backend\Modules\Catalog\Domain\Product;

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
     * AddToCartDataTransferObject constructor.
     *
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->id = $product->getId();
        $this->amount = $product->getOrderQuantity();
        $this->product = $product;
        $this->quote = false;
        $this->overwrite = false;

        foreach ($product->getProductOptions() as $productOption) {
            $this->{'option_' . $productOption->getId()} = $productOption->getDefaultProductOptionValue();
        }
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        foreach ($this->product->getProductOptions() as $productOption) {
            if (!$productOption->isRequired()) {
                continue;
            }

            $fieldName = 'option_' . $productOption->getId();

            if (!$this->$fieldName) {
                $context->buildViolation('Veld verplicht')
                    ->atPath($fieldName)
                    ->addViolation();
            }
        }
    }
}

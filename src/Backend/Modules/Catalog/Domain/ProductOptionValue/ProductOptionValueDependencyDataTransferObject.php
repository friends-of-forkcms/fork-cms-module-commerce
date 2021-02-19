<?php

namespace Backend\Modules\Catalog\Domain\ProductOptionValue;

use Backend\Modules\Catalog\Domain\ProductOption\ProductOption;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class ProductOptionValueDependencyDataTransferObject
{
    /**
     * @var ProductOption
     */
    public $product_option;

    /**
     * @var ProductOptionValue[]
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $values;

    public $product;

    public function __construct(ProductOptionValue $productOptionValue = null)
    {
        $this->values = new ArrayCollection();

        if (!$productOptionValue) {
            return;
        }

        $this->product_option = $productOptionValue->getProductOption();
        $this->product = $this->product_option->getProduct();
        $this->values->add($productOptionValue);
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\ProductOptionValue;

use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class ProductOptionValueDependencyDataTransferObject
{
    public ProductOption $product_option;

    /**
     * @var Collection|ProductOptionValue[]
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public Collection $values;

    public Product $product;

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

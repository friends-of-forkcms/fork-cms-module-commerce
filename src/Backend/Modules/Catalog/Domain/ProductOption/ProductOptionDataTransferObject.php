<?php

namespace Backend\Modules\Catalog\Domain\ProductOption;

use Backend\Modules\Catalog\Domain\Product\Product;
use Symfony\Component\Validator\Constraints as Assert;

class ProductOptionDataTransferObject
{
    /**
     * @var ProductOption
     */
    protected $productOptionEntity;

    /**
     * @var int
     */
    public $id;

    /**
     * @var Product
     */
    public $product;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $title;

    /**
     * @var boolean
     */
    public $required;

    /**
     * @var integer
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $type;

    /**
     * @var string
     */
    public $placeholder;

    /**
     * @var int
     */
    public $sequence;

    public function __construct(ProductOption $productOption = null)
    {
        // Set some default values
        $this->productOptionEntity = $productOption;
        $this->required = false;

        if (!$this->hasExistingProductOption()) {
            return;
        }

        $this->id = $productOption->getId();
        $this->product = $productOption->getProduct();
        $this->title = $productOption->getTitle();
        $this->required = $productOption->isRequired();
        $this->type = $productOption->getType();
        $this->placeholder = $productOption->getPlaceholder();
        $this->sequence = $productOption->getSequence();
    }

    public function getProductOptionEntity(): ProductOption
    {
        return $this->productOptionEntity;
    }

    public function hasExistingProductOption(): bool
    {
        return $this->productOptionEntity instanceof ProductOption;
    }
}

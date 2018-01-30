<?php

namespace Backend\Modules\Catalog\Domain\ProductOptionValue;

use Backend\Modules\Catalog\Domain\ProductOption\ProductOption;
use Backend\Modules\Catalog\Domain\Vat\Vat;
use Common\Doctrine\Entity\Meta;
use Symfony\Component\Validator\Constraints as Assert;

class ProductOptionValueDataTransferObject
{
    /**
     * @var ProductOptionValue
     */
    protected $productOptionValueEntity;

    /**
     * @var int
     */
    public $id;

    /**
     * @var ProductOption
     */
    public $productOption;

    /**
     * @param Vat
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $vat;

    /**
     * @param string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $title;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $price;

    /**
     * @var boolean
     */
    public $default_value;

    /**
     * @var integer
     */
    public $sequence;

    /**
     * @var Meta
     */
    public $meta;

    public function __construct(ProductOptionValue $productOptionValue = null)
    {
        $this->productOptionValueEntity = $productOptionValue;
        $this->default_value = false;

        if (!$this->hasExistingProductOptionValue()) {
            return;
        }

        $this->id = $productOptionValue->getId();
        $this->productOption = $productOptionValue->getProductOption();
        $this->vat = $productOptionValue->getVat();
        $this->title = $productOptionValue->getTitle();
        $this->price = $productOptionValue->getPrice();
        $this->default_value = $productOptionValue->isDefaultValue();
        $this->sequence = $productOptionValue->getSequence();
    }

    public function getProductOptionValueEntity(): ProductOptionValue
    {
        return $this->productOptionValueEntity;
    }

    public function hasExistingProductOptionValue(): bool
    {
        return $this->productOptionValueEntity instanceof ProductOptionValue;
    }
}

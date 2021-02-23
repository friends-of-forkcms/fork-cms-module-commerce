<?php

namespace Backend\Modules\Commerce\Domain\SpecificationValue;

use Backend\Modules\Commerce\Domain\Product\Product;
use Common\Doctrine\Entity\Meta;
use Symfony\Component\Validator\Constraints as Assert;

class SpecificationValueDataTransferObject
{
    /**
     * @var SpecificationValue
     */
    protected $specificationValueEntity;

    /**
     * @var int
     */
    public $id;

    public $specification;

    /**
     * @param SpecificationValue
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $value;

    /**
     * @var Product
     */
    public $product;

    /**
     * @var integer
     */
    public $sequence;

    /**
     * @var Meta
     */
    public $meta;

    public function __construct(SpecificationValue $specificationValue = null)
    {
        $this->specificationValueEntity = $specificationValue;

        if (!$this->hasExistingSpecificationValue()) {
            return;
        }

        $this->id = $specificationValue->getId();
        $this->specification = $specificationValue->getSpecification();
        $this->value = $specificationValue->getValue();
        $this->meta = $specificationValue->getMeta();
        $this->sequence = $specificationValue->getSequence();
    }

    public function getSpecificationValueEntity(): SpecificationValue
    {
        return $this->specificationValueEntity;
    }

    public function hasExistingSpecificationValue(): bool
    {
        return $this->specificationValueEntity instanceof SpecificationValue;
    }
}

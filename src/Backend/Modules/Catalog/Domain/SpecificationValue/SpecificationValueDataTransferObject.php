<?php

namespace Backend\Modules\Catalog\Domain\SpecificationValue;

use Backend\Core\Language\Locale;
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

    public $product;

    /**
     * @var Meta
     */
    public $meta;

    public function __construct(SpecificationValue $specificationValue = null)
    {
        $this->specificationValueEntity = $specificationValue;

        if ( ! $this->hasExistingSpecificationValue()) {
            return;
        }

        $this->id            = $specificationValue->getId();
        $this->specification = $specificationValue->getSpecification();
        $this->value         = $specificationValue->getValue();
        $this->meta          = $specificationValue->getMeta();
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

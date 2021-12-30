<?php

namespace Backend\Modules\Commerce\Domain\SpecificationValue;

use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\Specification\Specification;
use Common\Doctrine\Entity\Meta;
use Symfony\Component\Validator\Constraints as Assert;

class SpecificationValueDataTransferObject
{
    protected ?SpecificationValue $specificationValueEntity;
    public ?int $id = null;
    public ?Specification $specification;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $value = null;
    public Product $product;
    public ?int $sequence;
    public ?Meta $meta;

    public function __construct(SpecificationValue $specificationValue = null)
    {
        $this->specificationValueEntity = $specificationValue;
        $this->sequence = null;

        if (!$this->hasExistingSpecificationValue()) {
            return;
        }

        $this->id = $this->specificationValueEntity->getId();
        $this->specification = $this->specificationValueEntity->getSpecification();
        $this->value = $this->specificationValueEntity->getValue();
        $this->meta = $this->specificationValueEntity->getMeta();
        $this->sequence = $this->specificationValueEntity->getSequence();
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

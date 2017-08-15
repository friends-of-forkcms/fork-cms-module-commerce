<?php

namespace Backend\Modules\Catalog\Domain\Specification;

use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValue;
use Common\Doctrine\Entity\Meta;
use Symfony\Component\Validator\Constraints as Assert;

class SpecificationDataTransferObject
{
    /**
     * @var Specification
     */
    protected $specificationEntity;

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $title;

    /**
     * @var Locale
     */
    public $locale;

    /**
     * @var Meta
     */
    public $meta;

    /**
     * @var int
     */
    public $sequence;

    /**
     * @var SpecificationValue[]
     */
    public $specification_values;

    /**
     * @var int
     */
    public $type;

    public function __construct(Specification $specification = null)
    {
        $this->specificationEntity = $specification;

        if ( ! $this->hasExistingSpecification()) {
            return;
        }

        $this->id                   = $specification->getId();
        $this->title                = $specification->getTitle();
        $this->locale               = $specification->getLocale();
        $this->meta                 = $specification->getMeta();
        $this->sequence             = $specification->getSequence();
        $this->type                 = $specification->getType();
        $this->specification_values = $specification->getSpecificationValues();
    }

    public function getSpecificationEntity(): Specification
    {
        return $this->specificationEntity;
    }

    public function hasExistingSpecification(): bool
    {
        return $this->specificationEntity instanceof Specification;
    }
}

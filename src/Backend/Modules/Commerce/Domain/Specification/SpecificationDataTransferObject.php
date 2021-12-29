<?php

namespace Backend\Modules\Commerce\Domain\Specification;

use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValue;
use Common\Doctrine\Entity\Meta;
use Common\Locale;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class SpecificationDataTransferObject
{
    protected ?Specification $specificationEntity = null;

    public int $id;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $title;
    public Locale $locale;
    public ?Meta $meta = null;
    public ?int $sequence;

    /**
     * @var Collection|SpecificationValue[]
     */
    public Collection $specification_values;

    public bool $filter;

    public function __construct(Specification $specification = null)
    {
        $this->specificationEntity = $specification;
        $this->specification_values = new ArrayCollection();
        $this->filter = false;
        $this->sequence = null;

        if (!$this->hasExistingSpecification()) {
            return;
        }

        $this->id = $this->specificationEntity->getId();
        $this->title = $this->specificationEntity->getTitle();
        $this->locale = $this->specificationEntity->getLocale();
        $this->meta = $this->specificationEntity->getMeta();
        $this->sequence = $this->specificationEntity->getSequence();
        $this->filter = $this->specificationEntity->isFilter();
        $this->specification_values = $this->specificationEntity->getSpecificationValues();
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

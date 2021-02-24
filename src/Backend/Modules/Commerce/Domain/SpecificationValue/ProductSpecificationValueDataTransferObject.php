<?php

namespace Backend\Modules\Commerce\Domain\SpecificationValue;

use Backend\Core\Engine\Model;
use Backend\Modules\Commerce\Domain\Specification\Specification;
use Common\Doctrine\Entity\Meta;
use Symfony\Component\Validator\Constraints as Assert;

class ProductSpecificationValueDataTransferObject
{
    public ?Specification $specification;

    /**
     * @param SpecificationValue
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public SpecificationValue $value;

    public $product;

    public function __construct(SpecificationValue $specificationValue = null)
    {
        if (!$specificationValue) {
            return;
        }

        $this->specification = $specificationValue->getSpecification();
        $this->value = $specificationValue;
    }

    public function getMeta(): Meta
    {
        $specificationValueRepository = Model::get('commerce.repository.specification_value');

        $meta = new Meta(
            '',
            false,
            '',
            false,
            $this->value->getValue(),
            false,
            $specificationValueRepository->getUrl($this->value->getValue(), $this->specification, null),
            false
        );

        return $meta;
    }
}

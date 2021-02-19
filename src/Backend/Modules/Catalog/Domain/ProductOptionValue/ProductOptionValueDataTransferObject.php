<?php

namespace Backend\Modules\Catalog\Domain\ProductOptionValue;

use Backend\Modules\Catalog\Domain\ProductOption\ProductOption;
use Backend\Modules\Catalog\Domain\Vat\Vat;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\Type as MediaGroupType;
use Common\Doctrine\Entity\Meta;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Backend\Modules\Catalog\Domain\ProductOptionValue\Constraints as ProductOptionValueAssert;

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
     * @Assert\NotBlank(message="err.FieldIsRequired", groups={"DefaultTypes"})
     */
    public $title;

    /**
     * @param int
     *
     * @ProductOptionValueAssert\OneOrMoreFilled(fields={"end"}, groups={"BetweenType"})
     */
    public $start;

    /**
     * @param int
     *
     * @ProductOptionValueAssert\OneOrMoreFilled(fields={"start"}, groups={"BetweenType"})
     */
    public $end;

    /**
     * @param string
     */
    public $sub_title;

    /**
     * @param string
     */
    public $sku;

    /**
     * @var float
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $price = 0.00;

    /**
     * @var float
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $percentage = 0.00;

    /**
     * @var int
     */
    public $width;

    /**
     * @var int
     */
    public $height;

    /**
     * @var float
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $impact_type = ProductOptionValue::IMPACT_TYPE_ADD;

    /**
     * @var boolean
     */
    public $default_value;

    /**
     * @var string
     */
    public $hex_value;

    /**
     * @var MediaGroup
     */
    public $image;

    /**
     * @var integer
     */
    public $sequence;

    /**
     * @var Meta
     */
    public $meta;

    /**
     * @var ProductOptionValue[]
     */
    public $dependencies;

    public function __construct(ProductOptionValue $productOptionValue = null)
    {
        $this->productOptionValueEntity = $productOptionValue;
        $this->default_value = false;
        $this->image = MediaGroup::create(MediaGroupType::fromString(MediaGroupType::IMAGE));
        $this->dependencies = new ArrayCollection();

        if (!$this->hasExistingProductOptionValue()) {
            return;
        }

        $this->id = $productOptionValue->getId();
        $this->productOption = $productOptionValue->getProductOption();
        $this->vat = $productOptionValue->getVat();
        $this->image = $productOptionValue->getImage();
        $this->title = $productOptionValue->getTitle();
        $this->start = $productOptionValue->getStart();
        $this->end = $productOptionValue->getEnd();
        $this->sub_title = $productOptionValue->getSubTitle();
        $this->sku = $productOptionValue->getSku();
        $this->price = $productOptionValue->getPrice();
        $this->percentage = $productOptionValue->getPercentage();
        $this->width = $productOptionValue->getWidth();
        $this->height = $productOptionValue->getHeight();
        $this->impact_type = $productOptionValue->getImpactType();
        $this->default_value = $productOptionValue->isDefaultValue();
        $this->hex_value = $productOptionValue->getHexValue();
        $this->sequence = $productOptionValue->getSequence();

        foreach ($productOptionValue->getDependencies() as $dependency) {
            if (!$this->dependencies->containsKey($dependency->getProductOption()->getId())) {
                $this->dependencies->set(
                    $dependency->getProductOption()->getId(),
                    new ProductOptionValueDependencyDataTransferObject($dependency)
                );

                continue;
            }

            $this->dependencies->get($dependency->getProductOption()->getId())->values->add($dependency);
        }

        // just a fallback
        if (!$this->image instanceof MediaGroup) {
            $this->image = MediaGroup::create(MediaGroupType::fromString(MediaGroupType::IMAGE));
        }
    }

    public function getProductOptionValueEntity(): ProductOptionValue
    {
        return $this->productOptionValueEntity;
    }

    public function hasExistingProductOptionValue(): bool
    {
        return $this->productOptionValueEntity instanceof ProductOptionValue;
    }

    public function copy()
    {
        $this->id = null;
        $this->productOptionValueEntity = null;
    }
}

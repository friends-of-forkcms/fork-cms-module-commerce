<?php

namespace Backend\Modules\Commerce\Domain\ProductOptionValue;

use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Backend\Modules\Commerce\Domain\ProductOptionValue\Constraints as ProductOptionValueAssert;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\Type as MediaGroupType;
use Common\Doctrine\Entity\Meta;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class ProductOptionValueDataTransferObject
{
    protected ?ProductOptionValue $productOptionValueEntity;
    public ?int $id;
    public ProductOption $productOption;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public Vat $vat;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired", groups={"DefaultTypes"})
     */
    public ?string $title;

    /**
     * @ProductOptionValueAssert\OneOrMoreFilled(fields={"end"}, groups={"BetweenType"})
     */
    public ?int $start;

    /**
     * @ProductOptionValueAssert\OneOrMoreFilled(fields={"start"}, groups={"BetweenType"})
     */
    public ?int $end;
    public ?string $sub_title;
    public ?string $sku;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public float $price = 0.00;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public float $percentage = 0.00;
    public ?int $width;
    public ?int $height;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public int $impact_type = ProductOptionValue::IMPACT_TYPE_ADD;
    public bool $default_value;
    public ?string $hex_value;
    public MediaGroup $image;
    public int $sequence;
    public Meta $meta;

    /**
     * @var Collection|ProductOptionValue[]
     */
    public Collection $dependencies;

    public function __construct(ProductOptionValue $productOptionValue = null)
    {
        $this->productOptionValueEntity = $productOptionValue;
        $this->default_value = false;
        $this->image = MediaGroup::create(MediaGroupType::fromString(MediaGroupType::IMAGE));
        $this->dependencies = new ArrayCollection();

        if (!$this->hasExistingProductOptionValue()) {
            return;
        }

        $this->id = $this->productOptionValueEntity->getId();
        $this->productOption = $this->productOptionValueEntity->getProductOption();
        $this->vat = $this->productOptionValueEntity->getVat();
        $this->image = $this->productOptionValueEntity->getImage();
        $this->title = $this->productOptionValueEntity->getTitle();
        $this->start = $this->productOptionValueEntity->getStart();
        $this->end = $this->productOptionValueEntity->getEnd();
        $this->sub_title = $this->productOptionValueEntity->getSubTitle();
        $this->sku = $this->productOptionValueEntity->getSku();
        $this->price = $this->productOptionValueEntity->getPrice();
        $this->percentage = $this->productOptionValueEntity->getPercentage();
        $this->width = $this->productOptionValueEntity->getWidth();
        $this->height = $this->productOptionValueEntity->getHeight();
        $this->impact_type = $this->productOptionValueEntity->getImpactType();
        $this->default_value = $this->productOptionValueEntity->isDefaultValue();
        $this->hex_value = $this->productOptionValueEntity->getHexValue();
        $this->sequence = $this->productOptionValueEntity->getSequence();

        foreach ($this->productOptionValueEntity->getDependencies() as $dependency) {
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

    public function copy(): void
    {
        $this->id = null;
        $this->productOptionValueEntity = null;
    }
}

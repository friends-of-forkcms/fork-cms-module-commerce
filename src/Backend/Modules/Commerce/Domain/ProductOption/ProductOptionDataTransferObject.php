<?php

namespace Backend\Modules\Commerce\Domain\ProductOption;

use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\ProductDimensionNotification\ProductDimensionNotification;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class ProductOptionDataTransferObject
{
    protected ?ProductOption $productOptionEntity;
    public ?int $id;
    public Product $product;
    public ?ProductOptionValue $parent_product_option_value;

    /**
     * @Assert\Valid
     */
    public Collection $dimension_notifications;

    public Collection $remove_dimension_notifications;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $title;
    public ?string $text;
    public bool $required;
    public bool $custom_value_allowed;
    public float $custom_value_price = 0.00;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public int $type;
    public ?string $placeholder;
    public ?string $prefix;
    public ?string $suffix;
    public int $sequence;

    public function __construct(ProductOption $productOption = null)
    {
        // Set some default values
        $this->productOptionEntity = $productOption;
        $this->required = false;
        $this->dimension_notifications = new ArrayCollection();
        $this->remove_dimension_notifications = new ArrayCollection();

        if (!$this->hasExistingProductOption()) {
            return;
        }

        $this->id = $this->productOptionEntity->getId();
        $this->product = $this->productOptionEntity->getProduct();
        $this->parent_product_option_value = $this->productOptionEntity->getParentProductOptionValue();
        $this->title = $this->productOptionEntity->getTitle();
        $this->text = $this->productOptionEntity->getText();
        $this->required = $this->productOptionEntity->isRequired();
        $this->custom_value_allowed = $this->productOptionEntity->isCustomValueAllowed();
        $this->custom_value_price = $this->productOptionEntity->getCustomValuePrice();
        $this->type = $this->productOptionEntity->getType();
        $this->placeholder = $this->productOptionEntity->getPlaceholder();
        $this->prefix = $this->productOptionEntity->getPrefix();
        $this->suffix = $this->productOptionEntity->getSuffix();
        $this->sequence = $this->productOptionEntity->getSequence();
        $this->dimension_notifications = $this->productOptionEntity->getDimensionNotifications();
    }

    public function setProductOptionEntity(ProductOption $productOptionEntity): void
    {
        $this->productOptionEntity = $productOptionEntity;
    }

    public function getProductOptionEntity(): ProductOption
    {
        return $this->productOptionEntity;
    }

    public function hasExistingProductOption(): bool
    {
        return $this->productOptionEntity instanceof ProductOption;
    }

    public function copy(): void
    {
        $this->id = null;
        $this->productOptionEntity = null;
    }

    public function addDimensionNotification(ProductDimensionNotification $dimensionNotification): void
    {
        $this->dimension_notifications->add($dimensionNotification);
    }

    public function removeDimensionNotification(ProductDimensionNotification $dimensionNotification): void
    {
        $this->remove_dimension_notifications->add($dimensionNotification);
    }
}

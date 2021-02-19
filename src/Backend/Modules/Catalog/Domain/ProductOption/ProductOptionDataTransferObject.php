<?php

namespace Backend\Modules\Catalog\Domain\ProductOption;

use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\ProductDimensionNotification\ProductDimensionNotification;
use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;

class ProductOptionDataTransferObject
{
    /**
     * @var ProductOption
     */
    protected $productOptionEntity;

    /**
     * @var int
     */
    public $id;

    /**
     * @var Product
     */
    public $product;

    /**
     * @var ProductOptionValue
     */
    public $parent_product_option_value;

    /**
     * @Assert\Valid
     *
     * @var PersistentCollection
     */
    public $dimension_notifications;

    /**
     * @var PersistentCollection
     */
    public $remove_dimension_notifications;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $title;

    /**
     * @var string
     */
    public $text;

    /**
     * @var boolean
     */
    public $required;

    /**
     * @var boolean
     */
    public $custom_value_allowed;

    /**
     * @var float
     */
    public $custom_value_price = 0.00;

    /**
     * @var integer
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $type;

    /**
     * @var string
     */
    public $placeholder;

    /**
     * @var string
     */
    public $prefix;

    /**
     * @var string
     */
    public $suffix;

    /**
     * @var int
     */
    public $sequence;

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

        $this->id = $productOption->getId();
        $this->product = $productOption->getProduct();
        $this->parent_product_option_value = $productOption->getParentProductOptionValue();
        $this->title = $productOption->getTitle();
        $this->text = $productOption->getText();
        $this->required = $productOption->isRequired();
        $this->custom_value_allowed = $productOption->isCustomValueAllowed();
        $this->custom_value_price = $productOption->getCustomValuePrice();
        $this->type = $productOption->getType();
        $this->placeholder = $productOption->getPlaceholder();
        $this->prefix = $productOption->getPrefix();
        $this->suffix = $productOption->getSuffix();
        $this->sequence = $productOption->getSequence();
        $this->dimension_notifications = $productOption->getDimensionNotifications();
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

    public function copy()
    {
        $this->id = null;
        $this->productOptionEntity = null;
    }

    public function addDimensionNotification(ProductDimensionNotification $dimensionNotification)
    {
        $this->dimension_notifications->add($dimensionNotification);
    }

    public function removeDimensionNotification(ProductDimensionNotification $dimensionNotification)
    {
        $this->remove_dimension_notifications->add($dimensionNotification);
    }
}

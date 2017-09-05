<?php

namespace Backend\Modules\Catalog\Domain\Product;

use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Brand\Brand;
use Backend\Modules\Catalog\Domain\Category\Category;
use Backend\Modules\Catalog\Domain\ProductSpecial\ProductSpecial;
use Backend\Modules\Catalog\Domain\StockStatus\StockStatus;
use Backend\Modules\Catalog\Domain\Vat\Vat;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\Type as MediaGroupType;
use Common\Doctrine\Entity\Meta;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;

class ProductDataTransferObject
{
    /**
     * @var Product
     */
    protected $productEntity;

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
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $summary;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $price;

    /**
     * @var integer
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $stock;

    /**
     * @var integer
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $order_quantity = 1;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $sku;

    /**
     * @var string
     */
    public $text;

    /**
     * @var Locale
     */
    public $locale;

    /**
     * @var Category
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $category;

    /**
     * @var Brand
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $brand;

    /**
     * @var Vat
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $vat;

    /**
     * @var StockStatus
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $stock_status;

    /**
     * @var boolean
     */
    public $from_stock = true;

    /**
     * @var Meta
     */
    public $meta;

    /**
     * @var Image
     */
    public $image;

    /**
     * @var PersistentCollection
     */
    public $specification_values;

    /**
     * @Assert\Valid
     *
     * @var PersistentCollection
     */
    public $specials;

    /**
     * @var PersistentCollection
     */
    public $remove_specials;

    /**
     * @var PersistentCollection
     */
    public $related_products;

    /**
     * @var PersistentCollection
     */
    public $remove_specification_values;

    /**
     * @var PersistentCollection
     */
    public $remove_related_products;

    /**
     * @var MediaGroup
     */
    public $images;

    /**
     * @var int
     */
    public $sequence;

    public function __construct(Product $product = null)
    {
        // Set some default values
        $this->productEntity               = $product;
        $this->specification_values        = new ArrayCollection();
        $this->remove_specification_values = new ArrayCollection();
        $this->specials                    = new ArrayCollection();
        $this->remove_specials             = new ArrayCollection();
        $this->related_products            = new ArrayCollection();
        $this->remove_related_products     = new ArrayCollection();
        $this->images                      = MediaGroup::create(MediaGroupType::fromString('image'));

        if ( ! $this->hasExistingProduct()) {
            return;
        }

        $this->id                   = $product->getId();
        $this->meta                 = $product->getMeta();
        $this->category             = $product->getCategory();
        $this->brand                = $product->getBrand();
        $this->vat                  = $product->getVat();
        $this->stock_status         = $product->getStockStatus();
        $this->title                = $product->getTitle();
        $this->summary              = $product->getSummary();
        $this->text                 = $product->getText();
        $this->locale               = $product->getLocale();
        $this->price                = $product->getPrice();
        $this->stock                = $product->getStock();
        $this->order_quantity       = $product->getOrderQuantity();
        $this->from_stock           = $product->isFromStock();
        $this->sku                  = $product->getSku();
        $this->sequence             = $product->getSequence();
        $this->specification_values = $product->getSpecificationValues();
        $this->specials             = $product->getSpecials();
        $this->images               = $product->getImages();
        $this->related_products     = $product->getRelatedProducts();

        // just a fallback
        if ( ! $this->images instanceof MediaGroup) {
            $this->images = MediaGroup::create(MediaGroupType::fromString('image'));
        }
    }

    public function getProductEntity(): Product
    {
        return $this->productEntity;
    }

    public function hasExistingProduct(): bool
    {
        return $this->productEntity instanceof Product;
    }

    public function addSpecificationValu($value)
    {
        $this->specification_values->add($value);
    }

    public function removeSpecificationValu($value)
    {
        $this->specification_values->remove($value->getId());
        $this->remove_specification_values->add($value);
    }

    public function addRelatedProduct(Product $product)
    {
        $this->related_products->add($product);
    }

    public function removeRelatedProduct(Product $product)
    {
        // for our current entity
        $this->related_products->removeElement($product);

        // for our update to remove this entity
        $this->remove_related_products->add($product);
    }

    public function addSpecial(ProductSpecial $special)
    {
        $this->specials->add($special);
    }

    public function removeSpecial(ProductSpecial $special)
    {
        // for our current entity
        $this->remove_specials->add($special);
    }
}

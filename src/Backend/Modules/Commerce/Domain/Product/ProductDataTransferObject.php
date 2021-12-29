<?php

namespace Backend\Modules\Commerce\Domain\Product;

use Backend\Core\Engine\Model;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Brand\Brand;
use Backend\Modules\Commerce\Domain\Category\Category;
use Backend\Modules\Commerce\Domain\ProductDimension\ProductDimension;
use Backend\Modules\Commerce\Domain\ProductDimensionNotification\ProductDimensionNotification;
use Backend\Modules\Commerce\Domain\ProductSpecial\ProductSpecial;
use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValue;
use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValueRepository;
use Backend\Modules\Commerce\Domain\StockStatus\StockStatus;
use Backend\Modules\Commerce\Domain\UpSellProduct\UpSellProduct;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\Type;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\Type as MediaGroupType;
use Common\Doctrine\Entity\Meta;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Money\Money;
use Symfony\Component\Validator\Constraints as Assert;

class ProductDataTransferObject
{
    protected ?Product $productEntity = null;
    public ?int $id = null;
    public bool $hidden = false;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public int $type = Product::TYPE_DEFAULT;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public int $min_width = 0;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public int $min_height = 0;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public int $max_width = 0;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public int $max_height = 0;
    public int $extra_production_width = 0;
    public int $extra_production_height = 0;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $title;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $summary = null;

    public float $weight;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?Money $price = null;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public int $stock;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public int $order_quantity = 1;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $sku;
    public ?string $ean13 = null;
    public ?string $isbn = null;
    public ?string $text = null;
    public ?string $dimension_instructions = null;
    public Locale $locale;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?Category $category = null;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?Brand $brand = null;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?Vat $vat = null;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public StockStatus $stock_status;
    public bool $from_stock = true;
    public ?Meta $meta;
    public Image $image;
    public Collection $specification_values;

    /**
     * @Assert\Valid(groups={"dimensions"})
     */
    public Collection $specials;
    public Collection $remove_specials;

    /**
     * @Assert\Valid
     */
    public Collection $dimensions;
    public Collection $remove_dimensions;

    /**
     * @Assert\Valid
     */
    public Collection $dimension_notifications;
    public Collection $remove_dimension_notifications;
    public Collection $related_products;
    public Collection $up_sell_products;
    public Collection $remove_up_sell_products;
    public Collection $remove_specification_values;
    public Collection $remove_related_products;
    public MediaGroup $images;
    public MediaGroup $downloads;
    public ?int $sequence;

    public function __construct(Product $product = null, Locale $locale)
    {
        // Set some default values
        $this->locale = $locale;
        $this->productEntity = $product;
        $this->specification_values = new ArrayCollection();
        $this->remove_specification_values = new ArrayCollection();
        $this->specials = new ArrayCollection();
        $this->remove_specials = new ArrayCollection();
        $this->dimensions = new ArrayCollection();
        $this->remove_dimensions = new ArrayCollection();
        $this->dimension_notifications = new ArrayCollection();
        $this->remove_dimension_notifications = new ArrayCollection();
        $this->related_products = new ArrayCollection();
        $this->up_sell_products = new ArrayCollection();
        $this->remove_up_sell_products = new ArrayCollection();
        $this->remove_related_products = new ArrayCollection();
        $this->images = MediaGroup::create(MediaGroupType::fromString(Type::IMAGE));
        $this->downloads = MediaGroup::create(MediaGroupType::fromString(Type::FILE));
        $this->weight = 0.00;
        $this->sequence = null;

        if (!$this->hasExistingProduct()) {
            return;
        }

        $this->id = $this->productEntity->getId();
        $this->meta = $this->productEntity->getMeta();
        $this->category = $this->productEntity->getCategory();
        $this->brand = $this->productEntity->getBrand();
        $this->vat = $this->productEntity->getVat();
        $this->stock_status = $this->productEntity->getStockStatus();
        $this->hidden = $this->productEntity->isHidden();
        $this->type = $this->productEntity->getType();
        $this->min_width = $this->productEntity->getMinWidth();
        $this->min_height = $this->productEntity->getMinHeight();
        $this->max_width = $this->productEntity->getMaxWidth();
        $this->max_height = $this->productEntity->getMaxHeight();
        $this->extra_production_width = $this->productEntity->getExtraProductionWidth();
        $this->extra_production_height = $this->productEntity->getExtraProductionHeight();
        $this->title = $this->productEntity->getTitle();
        $this->summary = $this->productEntity->getSummary();
        $this->text = $this->productEntity->getText();
        $this->dimension_instructions = $this->productEntity->getDimensionInstructions();
        $this->locale = $this->productEntity->getLocale();
        $this->weight = $this->productEntity->getWeight();
        $this->price = $this->productEntity->getPrice();
        $this->stock = $this->productEntity->getStock();
        $this->order_quantity = $this->productEntity->getOrderQuantity();
        $this->from_stock = $this->productEntity->isFromStock();
        $this->sku = $this->productEntity->getSku();
        $this->ean13 = $this->productEntity->getEan13();
        $this->isbn = $this->productEntity->getIsbn();
        $this->sequence = $this->productEntity->getSequence();
        $this->specification_values = $this->productEntity->getSpecificationValues();
        $this->specials = $this->productEntity->getSpecials();
        $this->dimensions = $this->productEntity->getDimensions();
        $this->dimension_notifications = $this->productEntity->getDimensionNotifications();
        $this->images = $this->productEntity->getImages();
        $this->downloads = $this->productEntity->getDownloads();
        $this->related_products = $this->productEntity->getRelatedProducts();
        $this->up_sell_products = $this->productEntity->getUpSellProducts();

        // just a fallback
        if (!$this->images instanceof MediaGroup) {
            $this->images = MediaGroup::create(MediaGroupType::fromString(Type::IMAGE));
        }

        // just a fallback
        if (!$this->downloads instanceof MediaGroup) {
            $this->downloads = MediaGroup::create(MediaGroupType::fromString(Type::FILE));
        }
    }

    public function setProductEntity(Product $productEntity): void
    {
        $this->productEntity = $productEntity;
    }

    public function getProductEntity(): Product
    {
        return $this->productEntity;
    }

    public function hasExistingProduct(): bool
    {
        return $this->productEntity instanceof Product;
    }

    public function copy(): void
    {
        $this->id = null;
        $this->productEntity = null;
    }

    public function addSpecificationValue(SpecificationValue $value): void
    {
        // If the specification value has no entity save a new one
        if (!$value->getId()) {
            /**
             * @var SpecificationValueRepository $specificationValueRepository
             */
            $specificationValueRepository = Model::get('commerce.repository.specification_value');

            $value->setSequence($specificationValueRepository->getNextSequence($value));
        }

        $this->specification_values->add($value);
    }

    public function removeSpecificationValue($value): void
    {
        $this->specification_values->remove($value->getId());
        $this->remove_specification_values->add($value);
    }

    public function addRelatedProduct(Product $product): void
    {
        $this->related_products->add($product);
    }

    public function removeRelatedProduct(Product $product): void
    {
        // for our current entity
        $this->related_products->removeElement($product);

        // for our update to remove this entity
        $this->remove_related_products->add($product);
    }

    public function addUpSellProduct(UpSellProduct $upSellProduct): void
    {
        $this->up_sell_products->add($upSellProduct);
    }

    public function removeUpSellProduct(UpSellProduct $upSellProduct): void
    {
        // for our current entity
        $this->up_sell_products->removeElement($upSellProduct);

        $this->remove_up_sell_products->add($upSellProduct);
    }

    public function addSpecial(ProductSpecial $special): void
    {
        $this->specials->add($special);
    }

    public function removeSpecial(ProductSpecial $special): void
    {
        // for our current entity
        $this->remove_specials->add($special);
    }

    public function addDimension(ProductDimension $dimension): void
    {
        if ($this->type !== Product::TYPE_DIMENSIONS) {
            return;
        }

        $this->dimensions->add($dimension);
    }

    public function removeDimension(ProductDimension $dimension): void
    {
        $this->remove_dimensions->add($dimension);
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

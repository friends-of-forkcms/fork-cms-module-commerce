<?php

namespace Backend\Modules\Catalog\Domain\Product;

use Backend\Modules\Catalog\Domain\Brand\Brand;
use Backend\Modules\Catalog\Domain\Cart\CartValue;
use Backend\Modules\Catalog\Domain\Category\Category;
use Backend\Modules\Catalog\Domain\ProductSpecial\ProductSpecial;
use Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValue;
use Backend\Modules\Catalog\Domain\StockStatus\StockStatus;
use Backend\Modules\Catalog\Domain\Vat\Vat;
use Common\Doctrine\Entity\Meta;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup;
use Common\Locale;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * @ORM\Table(name="catalog_products")
 * @ORM\Entity(repositoryClass="ProductRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Product
{
    // Define the sort orders
    const SORT_RANDOM = 'random';
    const SORT_PRICE_ASC = 'price-asc';
    const SORT_PRICE_DESC = 'price-desc';
    const SORT_CREATED_AT = 'create-at';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @var Meta
     *
     * @ORM\ManyToOne(targetEntity="Common\Doctrine\Entity\Meta",cascade={"remove", "persist"})
     * @ORM\JoinColumn(name="meta_id", referencedColumnName="id")
     */
    private $meta;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\Category\Category", inversedBy="products")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $category;

    /**
     * @var Brand
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\Brand\Brand", inversedBy="products")
     * @ORM\JoinColumn(name="brand_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $brand;

    /**
     * @var Vat
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\Vat\Vat")
     * @ORM\JoinColumn(name="vat_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $vat;

    /**
     * @var StockStatus
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\StockStatus\StockStatus")
     * @ORM\JoinColumn(name="stock_status_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $stock_status;

    /**
     * @var SpecificationValue[]
     *
     * @ORM\ManyToMany(targetEntity="Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValue", inversedBy="products", cascade={"remove", "persist"})
     * @ORM\JoinTable(
     *     name="catalog_products_specification_values",
     *     joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="specification_value_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    private $specification_values;

    /**
     * @var ProductSpecial[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\ProductSpecial\ProductSpecial", mappedBy="product", cascade={"remove", "persist"})
     */
    private $specials;

    /**
     * Many Products may have many related products.
     * @var Product[]
     *
     * @ORM\ManyToMany(targetEntity="Product")
     * @ORM\JoinTable(name="catalog_related_products",
     *     joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="related_product_id", referencedColumnName="id")}
     * )
     */
    private $related_products;

    /**
     * Many Users have many Users.
     * @ORM\ManyToMany(targetEntity="Product")
     * @ORM\JoinTable(name="catalog_up_sell_products",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="up_sell_product_id", referencedColumnName="id")}
     *      )
     */
    protected $up_sell_products;

    /**
     * @var CartValue[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\Cart\CartValue", mappedBy="product", cascade={"remove", "persist"})
     */
    private $cart_values;

    /**
     * @var Locale
     *
     * @ORM\Column(type="locale", name="language")
     */
    private $locale;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $sku;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $weight;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $price;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $stock;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $order_quantity;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $from_stock;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $summary;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $text;

    /**
     * @var MediaGroup
     *
     * @ORM\OneToOne(
     *      targetEntity="Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup",
     *      cascade="persist",
     *      orphanRemoval=true
     * )
     * @ORM\JoinColumn(
     *      name="imageGroupId",
     *      referencedColumnName="id",
     *      onDelete="cascade"
     * )
     */
    protected $images;

    /**
     * @ORM\Column(type="integer", length=11, nullable=true)
     */
    private $sequence;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", name="created_on")
     */
    private $createdOn;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", name="edited_on")
     */
    private $editedOn;

    /**
     * Current object active price
     *
     * @var float
     */
    private $activePrice;

    /**
     * @var boolean
     */
    private $hasActiveSpecialPrice = false;

    private function __construct(
        Meta $meta,
        Category $category,
        ?Brand $brand,
        Vat $vat,
        StockStatus $stock_status,
        Locale $locale,
        string $title,
        float $weight,
        float $price,
        int $stock,
        int $order_quantity,
        bool $from_stock,
        string $sku,
        string $summary,
        ?string $text,
        int $sequence,
        MediaGroup $images,
        $specification_values,
        $specials,
        $related_products,
        $up_sell_products
    ) {
        $this->cart_values          = new ArrayCollection();
        $this->meta                 = $meta;
        $this->category             = $category;
        $this->brand                = $brand;
        $this->vat                  = $vat;
        $this->stock_status         = $stock_status;
        $this->locale               = $locale;
        $this->title                = $title;
        $this->weight               = $weight;
        $this->price                = $price;
        $this->stock                = $stock;
        $this->order_quantity       = $order_quantity;
        $this->from_stock           = $from_stock;
        $this->sku                  = $sku;
        $this->summary              = $summary;
        $this->text                 = $text;
        $this->sequence             = $sequence;
        $this->images               = $images;
        $this->specification_values = $specification_values;
        $this->specials             = $specials;
        $this->related_products     = $related_products;
        $this->up_sell_products     = $up_sell_products;
    }

    public static function fromDataTransferObject(ProductDataTransferObject $dataTransferObject)
    {
        if ($dataTransferObject->hasExistingProduct()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(ProductDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->meta,
            $dataTransferObject->category,
            $dataTransferObject->brand,
            $dataTransferObject->vat,
            $dataTransferObject->stock_status,
            $dataTransferObject->locale,
            $dataTransferObject->title,
            $dataTransferObject->weight,
            $dataTransferObject->price,
            $dataTransferObject->stock,
            $dataTransferObject->order_quantity,
            $dataTransferObject->from_stock,
            $dataTransferObject->sku,
            $dataTransferObject->summary,
            $dataTransferObject->text,
            $dataTransferObject->sequence,
            $dataTransferObject->images,
            $dataTransferObject->specification_values,
            $dataTransferObject->specials,
            $dataTransferObject->related_products,
            $dataTransferObject->up_sell_products
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMeta(): ?Meta
    {
        return $this->meta;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    /**
     * @return Vat
     */
    public function getVat(): ?Vat
    {
        return $this->vat;
    }

    /**
     * @return StockStatus
     */
    public function getStockStatus(): StockStatus
    {
        return $this->stock_status;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    /**
     * @return int
     */
    public function getStock(): int
    {
        return $this->stock;
    }

    /**
     * @param int $stock
     */
    public function setStock(int $stock)
    {
        $this->stock = $stock;
    }

    /**
     * @return int
     */
    public function getOrderQuantity(): int
    {
        return $this->order_quantity;
    }

    /**
     * @return bool
     */
    public function isFromStock(): bool
    {
        return $this->from_stock;
    }

    /**
     * @param bool $from_stock
     */
    public function setFromStock(bool $from_stock)
    {
        $this->from_stock = $from_stock;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @return MediaGroup
     */
    public function getImages(): ?MediaGroup
    {
        return $this->images;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function setSequence($sequence): void
    {
        $this->sequence = $sequence;
    }

    /**
     * @return SpecificationValue[]
     */
    public function getSpecificationValues()
    {
        return $this->specification_values;
    }

    public function removeSpecificationValue(SpecificationValue $specificationValue)
    {
        $this->specification_values->removeElement($specificationValue);
    }

    /**
     * @return ProductSpecial[]
     */
    public function getSpecials()
    {
        return $this->specials;
    }

    /**
     * @return Product[]
     */
    public function getRelatedProducts()
    {
        return $this->related_products;
    }

    /**
     * @return Product[]
     */
    public function getUpSellProducts()
    {
        return $this->up_sell_products;
    }

    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }

    public function getEditedOn(): DateTime
    {
        return $this->editedOn;
    }

    public function addRelatedProduct(Product $product)
    {
        $this->related_products->add($product);
    }

    public function removeRelatedProduct(Product $product)
    {
        $this->related_products->removeElement($product);
    }

    /**
     * @return CartValue[]
     */
    public function getCartValues(): array
    {
        return $this->cart_values;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdOn = $this->editedOn = new DateTime();
    }

    private static function update(ProductDataTransferObject $dataTransferObject)
    {
        $product = $dataTransferObject->getProductEntity();

        $product->meta                 = $dataTransferObject->meta;
        $product->category             = $dataTransferObject->category;
        $product->brand                = $dataTransferObject->brand;
        $product->vat                  = $dataTransferObject->vat;
        $product->stock_status         = $dataTransferObject->stock_status;
        $product->locale               = $dataTransferObject->locale;
        $product->title                = $dataTransferObject->title;
        $product->weight               = $dataTransferObject->weight;
        $product->price                = $dataTransferObject->price;
        $product->stock                = $dataTransferObject->stock;
        $product->order_quantity       = $dataTransferObject->order_quantity;
        $product->from_stock           = $dataTransferObject->from_stock;
        $product->sku                  = $dataTransferObject->sku;
        $product->summary              = $dataTransferObject->summary;
        $product->text                 = $dataTransferObject->text;
        $product->sequence             = $dataTransferObject->sequence;
        $product->images               = $dataTransferObject->images;
        $product->specification_values = $dataTransferObject->specification_values;
        $product->specials             = $dataTransferObject->specials;
        $product->related_products     = $dataTransferObject->related_products;
        $product->up_sell_products     = $dataTransferObject->up_sell_products;

        return $product;
    }

    public function getDataTransferObject(): ProductDataTransferObject
    {
        return new ProductDataTransferObject($this);
    }

    /**
     * Get the frontend url based on the parent category
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->category->getUrl() . '/' . $this->meta->getUrl();
    }

    /**
     * Get the product thumbnail
     */
    public function getThumbnail()
    {
        if ($this->getImages() && $this->getImages()->hasConnectedItems()) {
            return $this->getImages()->getFirstConnectedMediaItem();
        }

        return null;
    }

    /**
     * Get the active price if is special or the current price
     *
     * @param bool $includeVat
     *
     * @return float
     */
    public function getActivePrice(bool $includeVat = true): float
    {
        $this->calculateActivePrice();

        $price = $this->activePrice;

        if ($includeVat) {
            $price += $price * $this->vat->getAsPercentage();
        }

        return $price;
    }

    /**
     * Get the old price
     *
     * @param bool $includeVat
     *
     * @return float
     */
    public function getOldPrice(bool $includeVat = true): float
    {
        $price = $this->price;

        if ($includeVat) {
            $price += $price * $this->vat->getAsPercentage();
        }

        return $price;
    }

    /**
     * Get the vat price only
     *
     * @return float
     */
    public function getVatPrice()
    {
        return $this->getActivePrice(false) * $this->vat->getAsPercentage();
    }

    /**
     * Check if product has a special price going on
     *
     * @return boolean
     */
    public function hasActiveSpecialPrice()
    {
        $this->calculateActivePrice();

        return $this->hasActiveSpecialPrice;
    }

    /**
     * Calculate the active price
     */
    private function calculateActivePrice(): void
    {
        if ($this->activePrice) {
            return;
        }

        $expr  = Criteria::expr();
        $today = (new \DateTime('now'))->setTime(0, 0, 0);
        $price = $this->getPrice();

        $criteria = Criteria::create()->where(
            $expr->andX(
                $expr->lte('startDate', $today),
                $expr->gte('endDate', $today)
            )
        )->orWhere(
            $expr->andX(
                $expr->lte('startDate', $today),
                $expr->isNull('endDate')
            )
        )->setMaxResults(1);

        $specialPrices = $this->specials->matching($criteria);

        if ($specialPrice = $specialPrices->first()) {
            $this->hasActiveSpecialPrice = true;
            $price                       = $specialPrice->getPrice();
        }

        $this->activePrice = $price;
    }
}

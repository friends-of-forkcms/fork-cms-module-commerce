<?php

namespace Backend\Modules\Catalog\Domain\Product;

use Backend\Modules\Catalog\Domain\Brand\Brand;
use Backend\Modules\Catalog\Domain\Category\Category;
use Backend\Modules\Catalog\Domain\ProductSpecial\ProductSpecial;
use Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValue;
use Backend\Modules\Catalog\Domain\StockStatus\StockStatus;
use Backend\Modules\Catalog\Domain\Vat\Vat;
use Common\Doctrine\Entity\Meta;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup;
use Common\Locale;
use DateTime;
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
     * @ORM\ManyToMany(targetEntity="Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValue", cascade={"remove", "persist"})
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

    private function __construct(
        Meta $meta,
        Category $category,
        ?Brand $brand,
        Vat $vat,
        StockStatus $stock_status,
        Locale $locale,
        string $title,
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
        $related_products
    ) {
        $this->meta                   = $meta;
        $this->category               = $category;
        $this->brand                  = $brand;
        $this->vat                    = $vat;
        $this->stock_status           = $stock_status;
        $this->locale                 = $locale;
        $this->title                  = $title;
        $this->price                  = $price;
        $this->stock                  = $stock;
        $this->order_quantity = $order_quantity;
        $this->from_stock             = $from_stock;
        $this->sku                    = $sku;
        $this->summary                = $summary;
        $this->text                   = $text;
        $this->sequence               = $sequence;
        $this->images                 = $images;
        $this->specification_values   = $specification_values;
        $this->specials               = $specials;
        $this->related_products       = $related_products;
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
            $dataTransferObject->related_products
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
     * Get the active price if is special or the current price
     *
     * @param bool $includeVat
     *
     * @return float
     */
    public function getActivePrice(bool $includeVat = true): float
    {
        if ( ! $this->activePrice) {
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
                $price = $specialPrice->getPrice();
            }

            $this->activePrice = $price;
        }

        $price = $this->activePrice;

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
     * @return mixed
     */
    public function getImages()
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

    public function removeSpecificationValue(SpecificationValue $specificationValue) {
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
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdOn = $this->editedOn = new DateTime();
    }

    private static function update(ProductDataTransferObject $dataTransferObject)
    {
        $product = $dataTransferObject->getProductEntity();

        $product->meta                   = $dataTransferObject->meta;
        $product->category               = $dataTransferObject->category;
        $product->brand                  = $dataTransferObject->brand;
        $product->vat                    = $dataTransferObject->vat;
        $product->stock_status           = $dataTransferObject->stock_status;
        $product->locale                 = $dataTransferObject->locale;
        $product->title                  = $dataTransferObject->title;
        $product->price                  = $dataTransferObject->price;
        $product->stock                  = $dataTransferObject->stock;
        $product->order_quantity         = $dataTransferObject->order_quantity;
        $product->from_stock             = $dataTransferObject->from_stock;
        $product->sku                    = $dataTransferObject->sku;
        $product->summary                = $dataTransferObject->summary;
        $product->text                   = $dataTransferObject->text;
        $product->sequence               = $dataTransferObject->sequence;
        $product->images                 = $dataTransferObject->images;
        $product->specification_values   = $dataTransferObject->specification_values;
        $product->specials               = $dataTransferObject->specials;
        $product->related_products       = $dataTransferObject->related_products;

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
        return $this->category->getUrl() .'/' . $this->meta->getUrl();
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\Product;

use Backend\Modules\Commerce\Domain\Brand\Brand;
use Backend\Modules\Commerce\Domain\Cart\CartValue;
use Backend\Modules\Commerce\Domain\Category\Category;
use Backend\Modules\Commerce\Domain\ProductDimension\ProductDimension;
use Backend\Modules\Commerce\Domain\ProductDimensionNotification\ProductDimensionNotification;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Backend\Modules\Commerce\Domain\ProductSpecial\ProductSpecial;
use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValue;
use Backend\Modules\Commerce\Domain\StockStatus\StockStatus;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup;
use Common\Doctrine\Entity\Meta;
use Common\Locale;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Money\Money;

/**
 * @ORM\Table(name="commerce_products")
 * @ORM\Entity(repositoryClass="ProductRepository")
 */
class Product
{
    // Define the sort orders
    public const SORT_STANDARD = 'standard';
    public const SORT_PRICE_ASC = 'price-asc';
    public const SORT_PRICE_DESC = 'price-desc';
    public const SORT_CREATED_AT = 'created-at';

    // Define product types
    public const TYPE_DEFAULT = 1;
    public const TYPE_DIMENSIONS = 2;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    public int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Common\Doctrine\Entity\Meta", cascade={"remove", "persist"})
     * @ORM\JoinColumn(name="metaId", referencedColumnName="id")
     */
    private ?Meta $meta;

    /**
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Category\Category", inversedBy="products")
     * @ORM\JoinColumn(name="categoryId", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Category $category;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Brand\Brand", inversedBy="products")
     * @ORM\JoinColumn(name="brandId", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?Brand $brand;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Vat\Vat")
     * @ORM\JoinColumn(name="vatId", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Vat $vat;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\StockStatus\StockStatus")
     * @ORM\JoinColumn(name="stockStatusId", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?StockStatus $stockStatus;

    /**
     * @var ProductOption[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\ProductOption\ProductOption", mappedBy="product", cascade={"remove", "persist"})
     * @ORM\OrderBy({"sequence": "ASC"})
     */
    private $productOptions;

    /**
     * @var SpecificationValue[]
     *
     * @ORM\ManyToMany(targetEntity="Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValue", inversedBy="products", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="commerce_products_specification_values",
     *     joinColumns={@ORM\JoinColumn(name="productId", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="specificationValueId", referencedColumnName="id")}
     * )
     * @ORM\OrderBy({"value": "ASC"})
     */
    private $specificationValues;

    /**
     * @var Collection<int, ProductSpecial>
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\ProductSpecial\ProductSpecial", mappedBy="product", cascade={"remove", "persist"})
     */
    private $specials;

    /**
     * @var Collection<int, ProductDimension>
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\ProductDimension\ProductDimension", mappedBy="product", cascade={"remove", "persist"})
     * @ORM\OrderBy({"width": "ASC", "height": "ASC"})
     */
    private $dimensions;

    /**
     * @var Collection<int, ProductDimensionNotification>
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\ProductDimensionNotification\ProductDimensionNotification", mappedBy="product", cascade={"remove", "persist"})
     * @ORM\OrderBy({"width": "ASC", "height": "ASC"})
     */
    private $dimensionNotifications;

    /**
     * Many Products may have many related products.
     *
     * @var Collection<Product>
     *
     * @ORM\ManyToMany(targetEntity="Product")
     * @ORM\JoinTable(name="commerce_related_products",
     *     joinColumns={@ORM\JoinColumn(name="productId", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="relatedProductId", referencedColumnName="id")}
     * )
     * @ORM\OrderBy({"sequence": "ASC"})
     */
    private $relatedProducts;

    /**
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\UpSellProduct\UpSellProduct", mappedBy="product", cascade={"persist", "remove"})
     * @ORM\OrderBy({"sequence": "ASC"})
     */
    protected $upsellProducts;

    /**
     * @var Collection<CartValue>
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\Cart\CartValue", mappedBy="product", cascade={"remove", "persist"})
     */
    private $cartValues;

    /**
     * @Gedmo\SortableGroup
     * @ORM\Column(type="locale", name="language")
     */
    private Locale $locale;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $hidden;

    /**
     * @ORM\Column(type="integer", options={"default": 1})
     */
    private int $type;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private int $minWidth;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private int $minHeight;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private int $maxWidth;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private int $maxHeight;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private int $extraProductionWidth;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private int $extraProductionHeight;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $sku;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $ean13;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $isbn;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private ?float $weight;

    /**
     * @ORM\Embedded(class="\Money\Money", columnPrefix="price")
     */
    private Money $price;

    /**
     * @ORM\Column(type="integer")
     */
    private int $stock;

    /**
     * @ORM\Column(type="integer")
     */
    private int $orderQuantity;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $fromStock;

    /**
     * @ORM\Column(type="text")
     */
    private string $summary;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $text;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $dimensionInstructions;

    /**
     * @ORM\OneToOne(targetEntity="Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="imageGroupId", referencedColumnName="id", onDelete="cascade")
     * @ORM\OrderBy({"sequence": "ASC"})
     */
    protected ?MediaGroup $images;

    /**
     * @ORM\OneToOne(targetEntity="Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="downloadGroupId", referencedColumnName="id", onDelete="cascade")
     * @ORM\OrderBy({"sequence": "ASC"})
     */
    protected ?MediaGroup $downloads;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer", length=11, nullable=true)
     */
    private ?int $sequence;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $updatedAt;

    /**
     * Current object active price.
     */
    private Money $activePrice;

    private bool $hasActiveSpecialPrice = false;

    private function __construct(
        Meta $meta,
        Category $category,
        ?Brand $brand,
        ?Vat $vat,
        StockStatus $stockStatus,
        Locale $locale,
        bool $hidden,
        int $type,
        int $minWidth,
        int $minHeight,
        int $maxWidth,
        int $maxHeight,
        int $extraProductionWidth,
        int $extraProductionHeight,
        string $title,
        float $weight,
        Money $price,
        int $stock,
        int $orderQuantity,
        bool $fromStock,
        string $sku,
        ?string $ean13,
        ?string $isbn,
        string $summary,
        ?string $text,
        ?string $dimensionInstructions,
        ?int $sequence,
        MediaGroup $images,
        MediaGroup $downloads,
        $specificationValues,
        $specials,
        $relatedProducts,
        $upsellProducts,
        $dimensions,
        $dimensionNotifications
    ) {
        $this->cartValues = new ArrayCollection();
        $this->meta = $meta;
        $this->category = $category;
        $this->brand = $brand;
        $this->vat = $vat;
        $this->stockStatus = $stockStatus;
        $this->locale = $locale;
        $this->hidden = $hidden;
        $this->type = $type;
        $this->dimensionInstructions = $dimensionInstructions;
        $this->minWidth = $minWidth;
        $this->minHeight = $minHeight;
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
        $this->extraProductionWidth = $extraProductionWidth;
        $this->extraProductionHeight = $extraProductionHeight;
        $this->type = $type;
        $this->title = $title;
        $this->weight = $weight;
        $this->price = $price;
        $this->stock = $stock;
        $this->orderQuantity = $orderQuantity;
        $this->fromStock = $fromStock;
        $this->sku = $sku;
        $this->ean13 = $ean13;
        $this->isbn = $isbn;
        $this->summary = $summary;
        $this->text = $text;
        $this->sequence = $sequence;
        $this->images = $images;
        $this->downloads = $downloads;
        $this->specificationValues = $specificationValues;
        $this->specials = $specials;
        $this->relatedProducts = $relatedProducts;
        $this->upsellProducts = $upsellProducts;
        $this->dimensions = $dimensions;
        $this->dimensionNotifications = $dimensionNotifications;
    }

    public static function fromDataTransferObject(ProductDataTransferObject $dataTransferObject): Product
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
            $dataTransferObject->hidden,
            $dataTransferObject->type,
            $dataTransferObject->min_width,
            $dataTransferObject->min_height,
            $dataTransferObject->max_width,
            $dataTransferObject->max_height,
            $dataTransferObject->extra_production_width,
            $dataTransferObject->extra_production_height,
            $dataTransferObject->title,
            $dataTransferObject->weight,
            $dataTransferObject->price,
            $dataTransferObject->stock,
            $dataTransferObject->order_quantity,
            $dataTransferObject->from_stock,
            $dataTransferObject->sku,
            $dataTransferObject->ean13,
            $dataTransferObject->isbn,
            $dataTransferObject->summary,
            $dataTransferObject->text,
            $dataTransferObject->dimension_instructions,
            $dataTransferObject->sequence,
            $dataTransferObject->images,
            $dataTransferObject->downloads,
            $dataTransferObject->specification_values,
            $dataTransferObject->specials,
            $dataTransferObject->related_products,
            $dataTransferObject->up_sell_products,
            $dataTransferObject->dimensions,
            $dataTransferObject->dimension_notifications
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

    public function getStockStatus(): StockStatus
    {
        return $this->stockStatus;
    }

    /**
     * @return Collection<int, ProductOption>|ProductOption[]
     */
    public function getProductOptions(): Collection
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->isNull('parentProductOptionValue'));

        return $this->productOptions->matching($criteria);
    }

    /**
     * @return Collection<int, ProductOption>|ProductOption[]
     */
    public function getProductOptionsWithSubOptions(): Collection
    {
        return $this->productOptions;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getMinWidth(): int
    {
        return $this->minWidth;
    }

    public function getMinHeight(): int
    {
        return $this->minHeight;
    }

    public function getMaxWidth(): int
    {
        return $this->maxWidth;
    }

    public function getMaxHeight(): int
    {
        return $this->maxHeight;
    }

    public function getExtraProductionWidth(): int
    {
        return $this->extraProductionWidth;
    }

    public function getExtraProductionHeight(): int
    {
        return $this->extraProductionHeight;
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

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock)
    {
        $this->stock = $stock;
    }

    public function getOrderQuantity(): int
    {
        return $this->orderQuantity;
    }

    public function isFromStock(): bool
    {
        return $this->fromStock;
    }

    public function setFromStock(bool $fromStock)
    {
        $this->fromStock = $fromStock;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @return string
     */
    public function getEan13(): ?string
    {
        return $this->ean13;
    }

    /**
     * @return string
     */
    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    /**
     * @return string
     */
    public function getSummary(): ?string
    {
        return $this->summary;
    }

    /**
     * @return string
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getDimensionInstructions(): ?string
    {
        return $this->dimensionInstructions;
    }

    /**
     * @return MediaGroup
     */
    public function getImages(): ?MediaGroup
    {
        return $this->images;
    }

    /**
     * @return MediaGroup
     */
    public function getDownloads(): ?MediaGroup
    {
        return $this->downloads;
    }

    public function getSequence(): ?int
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
        return $this->specificationValues;
    }

    public function removeSpecificationValue(SpecificationValue $specificationValue)
    {
        $this->specificationValues->removeElement($specificationValue);
    }

    /**
     * @return ProductSpecial[]
     */
    public function getSpecials()
    {
        return $this->specials;
    }

    /**
     * @return ProductDimension[]
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * @return ProductDimensionNotification[]
     */
    public function getDimensionNotifications(): Collection
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create()->where($expr->isNull('productOption'));

        return $this->dimensionNotifications->matching($criteria);
    }

    /**
     * @return Product[]
     */
    public function getRelatedProducts()
    {
        return $this->relatedProducts;
    }

    /**
     * @return Product[]
     */
    public function getUpsellProducts()
    {
        return $this->upsellProducts;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function addRelatedProduct(Product $product)
    {
        $this->relatedProducts->add($product);
    }

    public function removeRelatedProduct(Product $product)
    {
        $this->relatedProducts->removeElement($product);
    }

    /**
     * @return CartValue[]
     */
    public function getCartValues(): array
    {
        return $this->cartValues;
    }

    private static function update(ProductDataTransferObject $dataTransferObject)
    {
        $product = $dataTransferObject->getProductEntity();

        $product->meta = $dataTransferObject->meta;
        $product->category = $dataTransferObject->category;
        $product->brand = $dataTransferObject->brand;
        $product->vat = $dataTransferObject->vat;
        $product->stockStatus = $dataTransferObject->stock_status;
        $product->locale = $dataTransferObject->locale;
        $product->hidden = $dataTransferObject->hidden;
        $product->type = $dataTransferObject->type;
        $product->minWidth = $dataTransferObject->min_width;
        $product->minHeight = $dataTransferObject->min_height;
        $product->maxWidth = $dataTransferObject->max_width;
        $product->maxHeight = $dataTransferObject->max_height;
        $product->extraProductionWidth = $dataTransferObject->extra_production_width;
        $product->extraProductionHeight = $dataTransferObject->extra_production_height;
        $product->title = $dataTransferObject->title;
        $product->weight = $dataTransferObject->weight;
        $product->price = $dataTransferObject->price;
        $product->stock = $dataTransferObject->stock;
        $product->orderQuantity = $dataTransferObject->order_quantity;
        $product->fromStock = $dataTransferObject->from_stock;
        $product->sku = $dataTransferObject->sku;
        $product->ean13 = $dataTransferObject->ean13;
        $product->isbn = $dataTransferObject->isbn;
        $product->summary = $dataTransferObject->summary;
        $product->text = $dataTransferObject->text;
        $product->dimensionInstructions = $dataTransferObject->dimension_instructions;
        $product->sequence = $dataTransferObject->sequence;
        $product->images = $dataTransferObject->images;
        $product->downloads = $dataTransferObject->downloads;
        $product->specificationValues = $dataTransferObject->specification_values;
        $product->specials = $dataTransferObject->specials;
        $product->relatedProducts = $dataTransferObject->related_products;
        $product->upsellProducts = $dataTransferObject->up_sell_products;
        $product->dimensions = $dataTransferObject->dimensions;
        $product->dimensionNotifications = $dataTransferObject->dimension_notifications;

        return $product;
    }

    public function getDataTransferObject(): ProductDataTransferObject
    {
        return new ProductDataTransferObject($this, \Backend\Core\Language\Locale::workingLocale());
    }

    /**
     * Get the frontend url based on the parent category.
     */
    public function getUrl(): string
    {
        return $this->category->getUrl() . '/' . $this->meta->getUrl();
    }

    /**
     * Get the product thumbnail.
     */
    public function getThumbnail()
    {
        if ($this->getImages() && $this->getImages()->hasConnectedItems()) {
            return $this->getImages()->getFirstConnectedMediaItem();
        }

        return null;
    }

    /**
     * Get the active price if is special or the current price.
     */
    public function getActivePrice(bool $includeVat = true): Money
    {
        $this->calculateActivePrice();

        $price = $this->activePrice;

        if ($includeVat) {
            return $this->vat->calculateInclusiveAmountFor($price);
        }

        return $price;
    }

    /**
     * Get the old price.
     */
    public function getOldPrice(bool $includeVat = true): Money
    {
        $price = $this->price;

        if ($includeVat) {
            return $this->vat->calculateInclusiveAmountFor($price);
        }

        return $price;
    }

    /**
     * Calculate a percentage between the old price and the new price, so we can display "-11%" in the shop.
     */
    public function getDiscountPercentageFormatted(): string
    {
        $oldPrice = $this->getOldPrice(false);
        $activePrice = $this->getActivePrice(false);

        $percentage = $oldPrice->subtract($activePrice)->ratioOf($oldPrice) * 100;
        $trendSymbol = $activePrice->greaterThanOrEqual($oldPrice) ? '+' : '-';

        return $trendSymbol . abs(round($percentage)) . '%';
    }

    public function getVatPrice(): Money
    {
        return $this->getActivePrice(false)->multiply($this->vat->getAsPercentage());
    }

    /**
     * Check if product has a special price going on at the moment.
     */
    public function hasActiveSpecialPrice(): bool
    {
        $this->calculateActivePrice();

        return $this->hasActiveSpecialPrice;
    }

    /**
     * Check if the product is in stock.
     */
    public function inStock(): bool
    {
        if (!$this->isFromStock()) {
            return true;
        }

        return $this->getStock() > 0;
    }

    /**
     * Calculate the active price.
     */
    private function calculateActivePrice(): void
    {
        // Do not recalculate if price is set already
        if (isset($this->activePrice)) {
            return;
        }

        $today = (new DateTime('now'))->setTime(0, 0, 0);
        $price = $this->getPrice();

        if ($this->type === self::TYPE_DIMENSIONS) {
            $criteria = Criteria::create()->orderBy([
                'price' => Criteria::ASC,
            ])->setMaxResults(1);

            /**
             * @var ProductDimension
             */
            $dimension = $this->dimensions->matching($criteria)->first();

            if ($dimension) {
                $price = $dimension->getPrice();
            }
        }

        $expr = Criteria::expr();
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

        /** @var Collection<int, ProductSpecial> $specialPrices */
        $specialPrices = $this->specials->matching($criteria);

        if ($specialPrice = $specialPrices->first()) {
            $this->hasActiveSpecialPrice = true;
            $price = $specialPrice->getPrice();
        }

        $this->activePrice = $price;
    }

    public function usesDimensions(): bool
    {
        return $this->type === self::TYPE_DIMENSIONS;
    }

    public function getDimensionNotificationByDimension(int $width, int $height): ?ProductDimensionNotification
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create()->where($expr->lte('width', $width))
            ->orWhere($expr->lte('height', $height))
            ->orderBy(['width' => Criteria::DESC, 'height' => Criteria::DESC])
            ->setMaxResults(1);

        $dimensionNotifications = $this->dimensionNotifications->matching($criteria)->first();

        return $dimensionNotifications ?: null;
    }

    /**
     * @return ProductDimensionNotification[]
     */
    public function getAllDimensionNotificationsByDimension(int $width, int $height): array
    {
        $notifications = [];

        if (!$this->usesDimensions()) {
            return $notifications;
        }

        if ($notification = $this->getDimensionNotificationByDimension($width, $height)) {
            $notifications[] = $notification;
        }

        foreach ($this->productOptions as $productOption) {
            $notifications = [...$notifications, ...$productOption->getAllDimensionNotificationsByDimension($width, $height)];
        }

        return $notifications;
    }
}

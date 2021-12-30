<?php

namespace Backend\Modules\Commerce\Domain\ProductOption;

use Backend\Modules\Commerce\Domain\Cart\CartValueOption;
use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\ProductDimensionNotification\ProductDimensionNotification;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Money\Money;

/**
 * @ORM\Table(name="commerce_product_options")
 * @ORM\Entity(repositoryClass="ProductOptionRepository")
 */
class ProductOption
{
    // Define the display type
    public const DISPLAY_TYPE_DROP_DOWN = 1;
    public const DISPLAY_TYPE_RADIO_BUTTON = 2;
    public const DISPLAY_TYPE_COLOR = 3;
    public const DISPLAY_TYPE_SQUARE_UNIT = 4;
    public const DISPLAY_TYPE_BETWEEN = 5;
    public const DISPLAY_TYPE_TEXT = 6;
    public const DISPLAY_TYPE_PIECE = 7;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Product\Product", inversedBy="productOptions")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Product $product;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue", inversedBy="productOptions")
     * @ORM\JoinColumn(name="productOptionValueId", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private ?ProductOptionValue $parentProductOptionValue;

    /**
     * @var Collection|ProductOptionValue[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue", mappedBy="productOption", cascade={"remove", "persist"})
     * @ORM\OrderBy({"sequence": "ASC"})
     */
    private Collection $productOptionValues;

    /**
     * @var Collection|ProductDimensionNotification[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\ProductDimensionNotification\ProductDimensionNotification", mappedBy="productOption", cascade={"remove", "persist"})
     * @ORM\OrderBy({"width": "ASC", "height": "ASC"})
     */
    private Collection $dimensionNotifications;

    /**
     * @var Collection|CartValueOption[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\Cart\CartValueOption", mappedBy="productOption", cascade={"remove", "persist"})
     */
    private Collection $cartValueOptions;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $text;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $required;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $customValueAllowed;

    /**
     * @ORM\Embedded(class="\Money\Money", columnPrefix="customValuePrice")
     */
    private Money $customValuePrice;

    /**
     * @ORM\Column(type="integer")
     */
    private int $type;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private ?int $sequence;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $placeholder;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $prefix;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $suffix;

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

    private function __construct(
        Product $product,
        ?ProductOptionValue $parentProductOptionValue,
        string $title,
        ?string $text,
        bool $required,
        bool $customValueAllowed,
        Money $customValuePrice,
        int $type,
        ?int $sequence,
        ?string $placeholder,
        ?string $prefix,
        ?string $suffix,
        $dimensionNotifications
    ) {
        $this->product = $product;
        $this->parentProductOptionValue = $parentProductOptionValue;
        $this->title = $title;
        $this->text = $text;
        $this->required = $required;
        $this->customValueAllowed = $customValueAllowed;
        $this->customValuePrice = $customValuePrice;
        $this->type = $type;
        $this->sequence = $sequence;
        $this->placeholder = $placeholder;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
        $this->dimensionNotifications = $dimensionNotifications;
    }

    public static function fromDataTransferObject(ProductOptionDataTransferObject $dataTransferObject): ProductOption
    {
        if ($dataTransferObject->hasExistingProductOption()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(ProductOptionDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->product,
            $dataTransferObject->parent_product_option_value,
            $dataTransferObject->title,
            $dataTransferObject->text,
            $dataTransferObject->required,
            $dataTransferObject->custom_value_allowed,
            $dataTransferObject->custom_value_price,
            $dataTransferObject->type,
            $dataTransferObject->sequence,
            $dataTransferObject->placeholder,
            $dataTransferObject->prefix,
            $dataTransferObject->suffix,
            $dataTransferObject->dimension_notifications
        );
    }

    private static function update(ProductOptionDataTransferObject $dataTransferObject): ProductOption
    {
        $productOption = $dataTransferObject->getProductOptionEntity();

        $productOption->product = $dataTransferObject->product;
        $productOption->parentProductOptionValue = $dataTransferObject->parent_product_option_value;
        $productOption->title = $dataTransferObject->title;
        $productOption->text = $dataTransferObject->text;
        $productOption->required = $dataTransferObject->required;
        $productOption->customValueAllowed = $dataTransferObject->custom_value_allowed;
        $productOption->customValuePrice = $dataTransferObject->custom_value_price;
        $productOption->type = $dataTransferObject->type;
        $productOption->sequence = $dataTransferObject->sequence;
        $productOption->placeholder = $dataTransferObject->placeholder;
        $productOption->prefix = $dataTransferObject->prefix;
        $productOption->suffix = $dataTransferObject->suffix;
        $productOption->dimensionNotifications = $dataTransferObject->dimension_notifications;

        return $productOption;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getParentProductOptionValue(): ?ProductOptionValue
    {
        return $this->parentProductOptionValue;
    }

    /**
     * @return Collection|ProductOptionValue[]
     */
    public function getProductOptionValues(): Collection
    {
        return $this->productOptionValues;
    }

    /**
     * @return ProductDimensionNotification[]
     */
    public function getDimensionNotifications()
    {
        return $this->dimensionNotifications;
    }

    /**
     * @return Collection|CartValueOption[]
     */
    public function getCartValueOptions(): Collection
    {
        return $this->cartValueOptions;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function isCustomValueAllowed(): bool
    {
        return $this->customValueAllowed;
    }

    public function getCustomValuePrice(): Money
    {
        return $this->customValuePrice;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getSequence(): ?int
    {
        return $this->sequence;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getDataTransferObject(): ProductOptionDataTransferObject
    {
        return new ProductOptionDataTransferObject($this);
    }

    /**
     * Get the default product option value when it exists.
     */
    public function getDefaultProductOptionValue(): ?ProductOptionValue
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('defaultValue', true))
            ->setMaxResults(1);

        $values = $this->productOptionValues->matching($criteria);

        if ($value = $values->first()) {
            return $value;
        }

        return null;
    }

    public function isColorType(): bool
    {
        return $this->type === self::DISPLAY_TYPE_COLOR;
    }

    public function isSquareUnitType(): bool
    {
        return $this->type === self::DISPLAY_TYPE_SQUARE_UNIT;
    }

    public function isPieceType(): bool
    {
        return $this->type === self::DISPLAY_TYPE_PIECE;
    }

    public function isBetweenType(): bool
    {
        return $this->type === self::DISPLAY_TYPE_BETWEEN;
    }

    public function isTextType(): bool
    {
        return $this->type === self::DISPLAY_TYPE_TEXT;
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

    public function getAllDimensionNotificationsByDimension(int $width, int $height): array
    {
        $notifications = [];

        if ($notification = $this->getDimensionNotificationByDimension($width, $height)) {
            $notifications[] = $notification;
        }

        foreach ($this->productOptionValues as $productOptionValue) {
            foreach ($productOptionValue->getProductOptions() as $productOption) {
                $notifications = [...$notifications, ...$productOption->getAllDimensionNotificationsByDimension($width, $height)];
            }
        }

        return $notifications;
    }
}

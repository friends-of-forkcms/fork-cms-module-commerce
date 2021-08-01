<?php

namespace Backend\Modules\Commerce\Domain\ProductOption;

use Backend\Modules\Commerce\Domain\Cart\CartValueOption;
use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\ProductDimensionNotification\ProductDimensionNotification;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Money\Money;

/**
 * @ORM\Table(name="commerce_product_options")
 * @ORM\Entity(repositoryClass="ProductOptionRepository")
 * @ORM\HasLifecycleCallbacks
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
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Product\Product", inversedBy="product_options")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Product $product;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue", inversedBy="product_options")
     * @ORM\JoinColumn(name="product_option_value_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private ?ProductOptionValue $parent_product_option_value;

    /**
     * @var Collection|ProductOptionValue[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue", mappedBy="product_option", cascade={"remove", "persist"})
     * @ORM\OrderBy({"sequence": "ASC"})
     */
    private Collection $product_option_values;

    /**
     * @var Collection|ProductDimensionNotification[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\ProductDimensionNotification\ProductDimensionNotification", mappedBy="product_option", cascade={"remove", "persist"})
     * @ORM\OrderBy({"width": "ASC", "height": "ASC"})
     */
    private Collection $dimension_notifications;

    /**
     * @var Collection|CartValueOption[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\Cart\CartValueOption", mappedBy="product_option", cascade={"remove", "persist"})
     */
    private Collection $cart_value_options;

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
    private bool $custom_value_allowed;

    /**
     * @ORM\Embedded(class="\Money\Money")
     */
    private Money $custom_value_price;

    /**
     * @ORM\Column(type="integer")
     */
    private int $type;

    /**
     * @ORM\Column(type="integer")
     */
    private int $sequence;

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
     * @ORM\Column(type="datetime", name="created_on")
     */
    private DateTimeInterface $createdOn;

    /**
     * @ORM\Column(type="datetime", name="edited_on")
     */
    private DateTimeInterface $editedOn;

    private function __construct(
        Product $product,
        ?ProductOptionValue $parent_product_option_value,
        string $title,
        ?string $text,
        bool $required,
        bool $custom_value_allowed,
        Money $custom_value_price,
        int $type,
        int $sequence,
        ?string $placeholder,
        ?string $prefix,
        ?string $suffix,
        $dimension_notifications
    ) {
        $this->product = $product;
        $this->parent_product_option_value = $parent_product_option_value;
        $this->title = $title;
        $this->text = $text;
        $this->required = $required;
        $this->custom_value_allowed = $custom_value_allowed;
        $this->custom_value_price = $custom_value_price;
        $this->type = $type;
        $this->sequence = $sequence;
        $this->placeholder = $placeholder;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
        $this->dimension_notifications = $dimension_notifications;
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
        $productOption->parent_product_option_value = $dataTransferObject->parent_product_option_value;
        $productOption->title = $dataTransferObject->title;
        $productOption->text = $dataTransferObject->text;
        $productOption->required = $dataTransferObject->required;
        $productOption->custom_value_allowed = $dataTransferObject->custom_value_allowed;
        $productOption->custom_value_price = $dataTransferObject->custom_value_price;
        $productOption->type = $dataTransferObject->type;
        $productOption->sequence = $dataTransferObject->sequence;
        $productOption->placeholder = $dataTransferObject->placeholder;
        $productOption->prefix = $dataTransferObject->prefix;
        $productOption->suffix = $dataTransferObject->suffix;
        $productOption->dimension_notifications = $dataTransferObject->dimension_notifications;

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
        return $this->parent_product_option_value;
    }

    /**
     * @return Collection|ProductOptionValue[]
     */
    public function getProductOptionValues(): Collection
    {
        return $this->product_option_values;
    }

    /**
     * @return ProductDimensionNotification[]
     */
    public function getDimensionNotifications()
    {
        return $this->dimension_notifications;
    }

    /**
     * @return Collection|CartValueOption[]
     */
    public function getCartValueOptions(): Collection
    {
        return $this->cart_value_options;
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
        return $this->custom_value_allowed;
    }

    public function getCustomValuePrice(): Money
    {
        return $this->custom_value_price;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getSequence(): int
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

    public function getCreatedOn(): DateTimeInterface
    {
        return $this->createdOn;
    }

    public function getEditedOn(): DateTimeInterface
    {
        return $this->editedOn;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        $this->createdOn = $this->editedOn = new DateTime();
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
        $criteria->where(Criteria::expr()->eq('default_value', true))
            ->setMaxResults(1);

        $values = $this->product_option_values->matching($criteria);

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

        $dimensionNotifications = $this->dimension_notifications->matching($criteria)->first();

        return $dimensionNotifications ?: null;
    }

    public function getAllDimensionNotificationsByDimension(int $width, int $height): array
    {
        $notifications = [];

        if ($notification = $this->getDimensionNotificationByDimension($width, $height)) {
            $notifications[] = $notification;
        }

        foreach ($this->product_option_values as $productOptionValue) {
            foreach ($productOptionValue->getProductOptions() as $productOption) {
                $notifications = [...$notifications, ...$productOption->getAllDimensionNotificationsByDimension($width, $height)];
            }
        }

        return $notifications;
    }
}

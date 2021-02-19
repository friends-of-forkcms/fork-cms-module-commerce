<?php

namespace Backend\Modules\Catalog\Domain\ProductOption;

use Backend\Modules\Catalog\Domain\Cart\CartValueOption;
use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\ProductDimensionNotification\ProductDimensionNotification;
use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_product_options")
 * @ORM\Entity(repositoryClass="ProductOptionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductOption
{
    // Define the display type
    const DISPLAY_TYPE_DROP_DOWN = 1;
    const DISPLAY_TYPE_RADIO_BUTTON = 2;
    const DISPLAY_TYPE_COLOR = 3;
    const DISPLAY_TYPE_SQUARE_UNIT = 4;
    const DISPLAY_TYPE_BETWEEN = 5;
    const DISPLAY_TYPE_TEXT = 6;
    const DISPLAY_TYPE_PIECE = 7;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\Product\Product", inversedBy="product_options")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $product;

    /**
     * @var ProductOptionValue
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue", inversedBy="product_options")
     * @ORM\JoinColumn(name="product_option_value_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $parent_product_option_value;

    /**
     * @var ProductOptionValue[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue", mappedBy="product_option", cascade={"remove", "persist"})
     * @ORM\OrderBy({"sequence" = "ASC"})
     */
    private $product_option_values;

    /**
     * @var ProductDimensionNotification[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\ProductDimensionNotification\ProductDimensionNotification", mappedBy="product_option", cascade={"remove", "persist"})
     * @ORM\OrderBy({"width" = "ASC", "height" = "ASC"})
     */
    private $dimension_notifications;

    /**
     * @var CartValueOption[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\Cart\CartValueOption", mappedBy="product_option", cascade={"remove", "persist"})
     */
    private $cart_value_options;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $text;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $required;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $custom_value_allowed;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2, options={"default" : 0.00})
     */
    private $custom_value_price;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $sequence;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $placeholder;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $prefix;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $suffix;

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

    private function __construct(
        Product $product,
        ?ProductOptionValue $parent_product_option_value,
        string $title,
        ?string $text,
        bool $required,
        bool $custom_value_allowed,
        float $custom_value_price,
        int $type,
        int $sequence,
        ?string $placeholder,
        ?string $prefix,
        ?string $suffix,
        $dimension_notifications
    )
    {
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

    public static function fromDataTransferObject(ProductOptionDataTransferObject $dataTransferObject)
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

    private static function update(ProductOptionDataTransferObject $dataTransferObject)
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

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @return ProductOptionValue
     */
    public function getParentProductOptionValue(): ?ProductOptionValue
    {
        return $this->parent_product_option_value;
    }

    /**
     * @return ProductOptionValue[]
     */
    public function getProductOptionValues()
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
     * @return CartValueOption[]
     */
    public function getCartValueOptions(): array
    {
        return $this->cart_value_options;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @return bool
     */
    public function isCustomValueAllowed(): bool
    {
        return $this->custom_value_allowed;
    }

    /**
     * @return float
     */
    public function getCustomValuePrice(): float
    {
        return $this->custom_value_price;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getSequence(): int
    {
        return $this->sequence;
    }

    /**
     * @return string
     */
    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    /**
     * @return string
     */
    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }

    public function getEditedOn(): DateTime
    {
        return $this->editedOn;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdOn = $this->editedOn = new DateTime();
    }

    public function getDataTransferObject(): ProductOptionDataTransferObject
    {
        return new ProductOptionDataTransferObject($this);
    }

    /**
     * Get the default product option value when it exists
     *
     * @return ProductOptionValue|null
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

    /**
     * @return bool
     */
    public function isColorType(): bool
    {
        return $this->type == self::DISPLAY_TYPE_COLOR;
    }

    /**
     * @return bool
     */
    public function isSquareUnitType(): bool
    {
        return $this->type == self::DISPLAY_TYPE_SQUARE_UNIT;
    }

    /**
     * @return bool
     */
    public function isPieceType(): bool
    {
        return $this->type == self::DISPLAY_TYPE_PIECE;
    }

    /**
     * @return bool
     */
    public function isBetweenType(): bool
    {
        return $this->type == self::DISPLAY_TYPE_BETWEEN;
    }

    /**
     * @return bool
     */
    public function isTextType(): bool
    {
        return $this->type == self::DISPLAY_TYPE_TEXT;
    }

    /**
     * @param int $width
     * @param int $height
     * @return ProductDimensionNotification|null
     */
    public function getDimensionNotificationByDimension(int $width, int $height): ?ProductDimensionNotification
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create()->where($expr->lte('width', $width))
            ->orWhere($expr->lte('height', $height))
            ->orderBy(['width' => Criteria::DESC, 'height' => Criteria::DESC])
            ->setMaxResults(1);

        $dimensionNotifications = $this->dimension_notifications->matching($criteria)->first();

        return $dimensionNotifications ? $dimensionNotifications : null;
    }
    /**
     * @param int $width
     * @param int $height
     * @return array
     */
    public function getAllDimensionNotificationsByDimension(int $width, int $height): array
    {
        $notifications = [];

        if ($notification = $this->getDimensionNotificationByDimension($width, $height)) {
            $notifications[] = $notification;
        }

        foreach ($this->product_option_values as $productOptionValue) {
            foreach ($productOptionValue->getProductOptions() as $productOption)
            $notifications = array_merge(
                $notifications,
                $productOption->getAllDimensionNotificationsByDimension($width, $height)
            );
        }

        return $notifications;
    }
}

<?php

namespace Backend\Modules\Catalog\Domain\ProductOptionValue;

use Backend\Modules\Catalog\Domain\ProductOption\ProductOption;
use Backend\Modules\Catalog\Domain\Vat\Vat;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup;
use Backend\Modules\MediaLibrary\Domain\MediaItem\MediaItem;
use DateTime;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_product_option_values")
 * @ORM\Entity(repositoryClass="ProductOptionValueRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductOptionValue
{
    const IMPACT_TYPE_ADD = 1;
    const IMPACT_TYPE_SUBTRACT = 2;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @var ProductOption
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\ProductOption\ProductOption", inversedBy="product_option_values")
     * @ORM\JoinColumn(name="product_option_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $product_option;

    /**
     * @var ProductOption[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\ProductOption\ProductOption", mappedBy="parent_product_option_value", cascade={"remove", "persist"})
     * @ORM\OrderBy({"sequence" = "ASC"})
     */
    private $product_options;

    /**
     * @var ProductOptionValue[]
     *
     * @ORM\ManyToMany(targetEntity="Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue", cascade={"remove", "persist"})
     * @ORM\JoinTable(name="catalog_product_option_values_dependencies",
     *      joinColumns={@ORM\JoinColumn(name="product_option_value_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="roduct_option_value_dependency_id", referencedColumnName="id")}
     *      )
     */
    private $dependencies;

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
     * @ORM\OrderBy({"sequence" = "ASC"})
     */
    protected $image;

    /**
     * @var Vat
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\Vat\Vat")
     * @ORM\JoinColumn(name="vat_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $vat;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $start;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $end;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sub_title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sku;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $price;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=1)
     */
    private $percentage;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $width;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $height;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $impact_type;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $default_value;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hex_value;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
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

    private function __construct(
        ProductOption $product_option,
        ?MediaGroup $image,
        Vat $vat,
        $dependencies,
        ?string $title,
        ?int $start,
        ?int $end,
        ?string $sub_title,
        ?string $sku,
        ?float $price,
        ?float $percentage,
        ?int $width,
        ?int $height,
        ?int $impact_type,
        bool $default_value,
        ?string $hex_value,
        int $sequence
    )
    {
        $this->product_option = $product_option;
        $this->image = $image;
        $this->vat = $vat;
        $this->dependencies = $dependencies;
        $this->title = $title;
        $this->start = $start;
        $this->end = $end;
        $this->sub_title = $sub_title;
        $this->sku = $sku;
        $this->price = $price;
        $this->percentage = $percentage;
        $this->width = $width;
        $this->height = $height;
        $this->impact_type = $impact_type;
        $this->default_value = $default_value;
        $this->hex_value = $hex_value;
        $this->sequence = $sequence;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return ProductOption
     */
    public function getProductOption(): ProductOption
    {
        return $this->product_option;
    }

    /**
     * @return ProductOption[]
     */
    public function getProductOptions()
    {
        return $this->product_options;
    }

    /**
     * @return MediaGroup
     */
    public function getImage(): ?MediaGroup
    {
        return $this->image;
    }

    /**
     * @return Vat
     */
    public function getVat(): Vat
    {
        return $this->vat;
    }

    /**
     * @return ProductOptionValue[]|PersistentCollection
     */
    public function getDependencies(): ?PersistentCollection
    {
        return $this->dependencies;
    }

    /**
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getStart(): ?int
    {
        return $this->start;
    }

    /**
     * @return int
     */
    public function getEnd(): ?int
    {
        return $this->end;
    }

    /**
     * @return string
     */
    public function getSubTitle(): ?string
    {
        return $this->sub_title;
    }

    /**
     * @return string
     */
    public function getSku(): ?string
    {
        return $this->sku;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return float
     */
    public function getPercentage(): float
    {
        return $this->percentage;
    }

    /**
     * @return int
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @return int
     */
    public function getImpactType(): int
    {
        if (!$this->impact_type) {
            $this->impact_type = self::IMPACT_TYPE_ADD;
        }

        return $this->impact_type;
    }

    /**
     * @return bool
     */
    public function isImpactTypeAdd(): bool
    {
        return $this->getImpactType() == self::IMPACT_TYPE_ADD;
    }

    /**
     * @return bool
     */
    public function isImpactTypeSubtract(): bool
    {
        return $this->getImpactType() == self::IMPACT_TYPE_SUBTRACT;
    }

    /**
     * @return bool
     */
    public function isDefaultValue(): bool
    {
        return $this->default_value;
    }

    /**
     * @return string
     */
    public function getHexValue(): ?string
    {
        return $this->hex_value;
    }

    /**
     * @return int
     */
    public function getSequence(): int
    {
        return $this->sequence;
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

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->editedOn = new DateTime();
    }

    public static function fromDataTransferObject(ProductOptionValueDataTransferObject $dataTransferObject)
    {
        if ($dataTransferObject->hasExistingProductOptionValue()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(ProductOptionValueDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->productOption,
            $dataTransferObject->image,
            $dataTransferObject->vat,
            $dataTransferObject->dependencies,
            $dataTransferObject->title,
            $dataTransferObject->start,
            $dataTransferObject->end,
            $dataTransferObject->sub_title,
            $dataTransferObject->sku,
            $dataTransferObject->price,
            $dataTransferObject->percentage,
            $dataTransferObject->width,
            $dataTransferObject->height,
            $dataTransferObject->impact_type,
            $dataTransferObject->default_value,
            $dataTransferObject->hex_value,
            $dataTransferObject->sequence
        );
    }

    private static function update(ProductOptionValueDataTransferObject $dataTransferObject)
    {
        $productOptionValue = $dataTransferObject->getProductOptionValueEntity();

        $productOptionValue->product_option = $dataTransferObject->productOption;
        $productOptionValue->image = $dataTransferObject->image;
        $productOptionValue->vat = $dataTransferObject->vat;
        $productOptionValue->dependencies = $dataTransferObject->dependencies;
        $productOptionValue->title = $dataTransferObject->title;
        $productOptionValue->start = $dataTransferObject->start;
        $productOptionValue->end = $dataTransferObject->end;
        $productOptionValue->sub_title = $dataTransferObject->sub_title;
        $productOptionValue->sku = $dataTransferObject->sku;
        $productOptionValue->price = $dataTransferObject->price;
        $productOptionValue->percentage = $dataTransferObject->percentage;
        $productOptionValue->width = $dataTransferObject->width;
        $productOptionValue->height = $dataTransferObject->height;
        $productOptionValue->impact_type = $dataTransferObject->impact_type;
        $productOptionValue->default_value = $dataTransferObject->default_value;
        $productOptionValue->hex_value = $dataTransferObject->hex_value;
        $productOptionValue->sequence = $dataTransferObject->sequence;

        return $productOptionValue;
    }

    public function getDataTransferObject(): ProductOptionValueDataTransferObject
    {
        return new ProductOptionValueDataTransferObject($this);
    }

    /**
     * Get the vat price only
     *
     * @return float
     */
    public function getVatPrice()
    {
        return $this->getPrice() * $this->vat->getAsPercentage();
    }

    /**
     * Get the product thumbnail
     *
     * @return MediaItem|null
     */
    public function getThumbnail(): ?MediaItem
    {
        if ($this->getImage() && $this->getImage()->hasConnectedItems()) {
            return $this->getImage()->getFirstConnectedMediaItem();
        }

        return null;
    }
}

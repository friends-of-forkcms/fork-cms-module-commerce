<?php

namespace Backend\Modules\Commerce\Domain\ProductOptionValue;

use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup;
use Backend\Modules\MediaLibrary\Domain\MediaItem\MediaItem;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Money\Money;

/**
 * @ORM\Table(name="commerce_product_option_values")
 * @ORM\Entity(repositoryClass="ProductOptionValueRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductOptionValue
{
    public const IMPACT_TYPE_ADD = 1;
    public const IMPACT_TYPE_SUBTRACT = 2;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\ProductOption\ProductOption", inversedBy="product_option_values")
     * @ORM\JoinColumn(name="product_option_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?ProductOption $product_option;

    /**
     * @var Collection|ProductOption[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\ProductOption\ProductOption", mappedBy="parent_product_option_value", cascade={"remove", "persist"})
     * @ORM\OrderBy({"sequence": "ASC"})
     */
    private Collection $product_options;

    /**
     * @var Collection|ProductOptionValue[]
     *
     * @ORM\ManyToMany(targetEntity="Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue", cascade={"remove", "persist"})
     * @ORM\JoinTable(name="commerce_product_option_values_dependencies",
     *     joinColumns={@ORM\JoinColumn(name="product_option_value_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="product_option_value_dependency_id", referencedColumnName="id")}
     * )
     */
    private Collection $dependencies;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     * @ORM\JoinColumn(
     *     name="imageGroupId",
     *     referencedColumnName="id",
     *     onDelete="cascade"
     * )
     * @ORM\OrderBy({"sequence": "ASC"})
     */
    protected ?MediaGroup $image;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Vat\Vat")
     * @ORM\JoinColumn(name="vat_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Vat $vat;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $title;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $start;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $end;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $sub_title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $sku;

    /**
     * @ORM\Embedded(class="\Money\Money")
     */
    private Money $price;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=1)
     */
    private float $percentage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $width;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $height;

    /**
     * @ORM\Column(type="integer")
     */
    private int $impact_type;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $default_value;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $hex_value;

    /**
     * @ORM\Column(type="integer")
     */
    private int $sequence;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="created_on", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdOn;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", name="edited_on", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $editedOn;

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
        ?Money $price,
        ?float $percentage,
        ?int $width,
        ?int $height,
        ?int $impact_type,
        bool $default_value,
        ?string $hex_value,
        int $sequence
    ) {
        $this->product_option = $product_option;
        $this->image = $image;
        $this->vat = $vat;
        $this->dependencies = $dependencies;
        $this->title = $title;
        $this->start = $start;
        $this->end = $end;
        $this->sub_title = $sub_title;
        $this->sku = $sku;
        $this->price = $price ?? Money::EUR(0);
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

    public function getProductOption(): ProductOption
    {
        return $this->product_option;
    }

    /**
     * @return Collection|ProductOption[]
     */
    public function getProductOptions(): Collection
    {
        return $this->product_options;
    }

    public function getImage(): ?MediaGroup
    {
        return $this->image;
    }

    public function getVat(): Vat
    {
        return $this->vat;
    }

    /**
     * @return Collection<int, ProductOptionValue>|ProductOptionValue[]
     */
    public function getDependencies(): ?Collection
    {
        return $this->dependencies;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getStart(): ?int
    {
        return $this->start;
    }

    public function getEnd(): ?int
    {
        return $this->end;
    }

    public function getSubTitle(): ?string
    {
        return $this->sub_title;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getPercentage(): float
    {
        return $this->percentage;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function getImpactType(): int
    {
        if (!$this->impact_type) {
            $this->impact_type = self::IMPACT_TYPE_ADD;
        }

        return $this->impact_type;
    }

    public function isImpactTypeAdd(): bool
    {
        return $this->getImpactType() === self::IMPACT_TYPE_ADD;
    }

    public function isImpactTypeSubtract(): bool
    {
        return $this->getImpactType() === self::IMPACT_TYPE_SUBTRACT;
    }

    public function isDefaultValue(): bool
    {
        return $this->default_value;
    }

    public function getHexValue(): ?string
    {
        return $this->hex_value;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function getCreatedOn(): DateTimeInterface
    {
        return $this->createdOn;
    }

    public function getEditedOn(): DateTimeInterface
    {
        return $this->editedOn;
    }

    public static function fromDataTransferObject(ProductOptionValueDataTransferObject $dataTransferObject): ProductOptionValue
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

    private static function update(ProductOptionValueDataTransferObject $dataTransferObject): ProductOptionValue
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

    public function getVatPrice(): Money
    {
        return $this->getPrice()->multiply($this->vat->getAsPercentage());
    }

    /**
     * Get the product thumbnail.
     */
    public function getThumbnail(): ?MediaItem
    {
        if ($this->getImage() && $this->getImage()->hasConnectedItems()) {
            return $this->getImage()->getFirstConnectedMediaItem();
        }

        return null;
    }
}

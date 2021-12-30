<?php

namespace Backend\Modules\Commerce\Domain\ProductOptionValue;

use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup;
use Backend\Modules\MediaLibrary\Domain\MediaItem\MediaItem;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Money\Money;

/**
 * @ORM\Table(name="commerce_product_option_values")
 * @ORM\Entity(repositoryClass="ProductOptionValueRepository")
 */
class ProductOptionValue
{
    public const IMPACT_TYPE_ADD = 1;
    public const IMPACT_TYPE_SUBTRACT = 2;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\ProductOption\ProductOption", inversedBy="productOptionValues")
     * @ORM\JoinColumn(name="productOptionId", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?ProductOption $productOption;

    /**
     * @var Collection|ProductOption[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\ProductOption\ProductOption", mappedBy="parentProductOptionValue", cascade={"remove", "persist"})
     * @ORM\OrderBy({"sequence": "ASC"})
     */
    private Collection $productOptions;

    /**
     * @var Collection|ProductOptionValue[]
     *
     * @ORM\ManyToMany(targetEntity="Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue", cascade={"remove", "persist"})
     * @ORM\JoinTable(name="commerce_product_option_values_dependencies",
     *     joinColumns={@ORM\JoinColumn(name="productOptionValueId", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="productOptionValueDependencyId", referencedColumnName="id")}
     * )
     */
    private Collection $dependencies;

    /**
     * @ORM\OneToOne(targetEntity="Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="imageGroupId", referencedColumnName="id", onDelete="cascade")
     * @ORM\OrderBy({"sequence": "ASC"})
     */
    protected ?MediaGroup $image;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Vat\Vat")
     * @ORM\JoinColumn(name="vatId", referencedColumnName="id", onDelete="CASCADE")
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
    private ?string $subTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $sku;

    /**
     * @ORM\Embedded(class="\Money\Money", columnPrefix="price")
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
    private int $impactType;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $defaultValue;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $hexValue;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
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

    private function __construct(
        ProductOption $productOption,
        ?MediaGroup $image,
        Vat $vat,
        $dependencies,
        ?string $title,
        ?int $start,
        ?int $end,
        ?string $subTitle,
        ?string $sku,
        ?Money $price,
        ?float $percentage,
        ?int $width,
        ?int $height,
        ?int $impactType,
        bool $defaultValue,
        ?string $hexValue,
        ?int $sequence
    ) {
        $this->productOption = $productOption;
        $this->image = $image;
        $this->vat = $vat;
        $this->dependencies = $dependencies;
        $this->title = $title;
        $this->start = $start;
        $this->end = $end;
        $this->subTitle = $subTitle;
        $this->sku = $sku;
        $this->price = $price ?? Money::EUR(0);
        $this->percentage = $percentage;
        $this->width = $width;
        $this->height = $height;
        $this->impactType = $impactType;
        $this->defaultValue = $defaultValue;
        $this->hexValue = $hexValue;
        $this->sequence = $sequence;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProductOption(): ProductOption
    {
        return $this->productOption;
    }

    /**
     * @return Collection|ProductOption[]
     */
    public function getProductOptions(): Collection
    {
        return $this->productOptions;
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
        return $this->subTitle;
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
        if (!$this->impactType) {
            $this->impactType = self::IMPACT_TYPE_ADD;
        }

        return $this->impactType;
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
        return $this->defaultValue;
    }

    public function getHexValue(): ?string
    {
        return $this->hexValue;
    }

    public function getSequence(): ?int
    {
        return $this->sequence;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
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

        $productOptionValue->productOption = $dataTransferObject->productOption;
        $productOptionValue->image = $dataTransferObject->image;
        $productOptionValue->vat = $dataTransferObject->vat;
        $productOptionValue->dependencies = $dataTransferObject->dependencies;
        $productOptionValue->title = $dataTransferObject->title;
        $productOptionValue->start = $dataTransferObject->start;
        $productOptionValue->end = $dataTransferObject->end;
        $productOptionValue->subTitle = $dataTransferObject->sub_title;
        $productOptionValue->sku = $dataTransferObject->sku;
        $productOptionValue->price = $dataTransferObject->price;
        $productOptionValue->percentage = $dataTransferObject->percentage;
        $productOptionValue->width = $dataTransferObject->width;
        $productOptionValue->height = $dataTransferObject->height;
        $productOptionValue->impactType = $dataTransferObject->impact_type;
        $productOptionValue->defaultValue = $dataTransferObject->default_value;
        $productOptionValue->hexValue = $dataTransferObject->hex_value;
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

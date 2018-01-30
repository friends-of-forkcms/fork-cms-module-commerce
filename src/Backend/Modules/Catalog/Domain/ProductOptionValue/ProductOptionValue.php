<?php

namespace Backend\Modules\Catalog\Domain\ProductOptionValue;

use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\ProductOption\ProductOption;
use Backend\Modules\Catalog\Domain\Vat\Vat;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_product_option_values")
 * @ORM\Entity(repositoryClass="ProductOptionValueRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductOptionValue
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
     * @var ProductOption
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\ProductOption\ProductOption", inversedBy="product_option_values")
     * @ORM\JoinColumn(name="product_option_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $product_option;

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
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $price;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $default_value;

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
        Vat $vat,
        string $title,
        ?float $price,
        bool $default_value,
        int $sequence
    )
    {
        $this->product_option = $product_option;
        $this->vat = $vat;
        $this->title = $title;
        $this->price = $price;
        $this->default_value = $default_value;
        $this->sequence = $sequence;
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
            $dataTransferObject->vat,
            $dataTransferObject->title,
            $dataTransferObject->price,
            $dataTransferObject->default_value,
            $dataTransferObject->sequence
        );
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
     * @return Vat
     */
    public function getVat(): Vat
    {
        return $this->vat;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return bool
     */
    public function isDefaultValue(): bool
    {
        return $this->default_value;
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

    private static function update(ProductOptionValueDataTransferObject $dataTransferObject)
    {
        $productOptionValue = $dataTransferObject->getProductOptionValueEntity();

        $productOptionValue->product_option = $dataTransferObject->productOption;
        $productOptionValue->vat = $dataTransferObject->vat;
        $productOptionValue->title = $dataTransferObject->title;
        $productOptionValue->price = $dataTransferObject->price;
        $productOptionValue->default_value = $dataTransferObject->default_value;
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
}

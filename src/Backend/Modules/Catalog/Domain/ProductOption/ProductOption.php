<?php

namespace Backend\Modules\Catalog\Domain\ProductOption;

use Backend\Modules\Catalog\Domain\Product\Product;
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
     * @var ProductOptionValue[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue", mappedBy="product_option", cascade={"remove", "persist"})
     * @ORM\OrderBy({"sequence" = "ASC"})
     */
    private $product_option_values;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $required;

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
        string $title,
        bool $required,
        int $type,
        int $sequence,
        ?string $placeholder
    )
    {
        $this->product = $product;
        $this->title = $title;
        $this->required = $required;
        $this->type = $type;
        $this->sequence = $sequence;
        $this->placeholder = $placeholder;
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
            $dataTransferObject->title,
            $dataTransferObject->required,
            $dataTransferObject->type,
            $dataTransferObject->sequence,
            $dataTransferObject->placeholder
        );
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
     * @return ProductOptionValue[]
     */
    public function getProductOptionValues()
    {
        return $this->product_option_values;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
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

    private static function update(ProductOptionDataTransferObject $dataTransferObject)
    {
        $productOption = $dataTransferObject->getProductOptionEntity();

        $productOption->product = $dataTransferObject->product;
        $productOption->title = $dataTransferObject->title;
        $productOption->required = $dataTransferObject->required;
        $productOption->type = $dataTransferObject->type;
        $productOption->sequence = $dataTransferObject->sequence;
        $productOption->placeholder = $dataTransferObject->placeholder;

        return $productOption;
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
}

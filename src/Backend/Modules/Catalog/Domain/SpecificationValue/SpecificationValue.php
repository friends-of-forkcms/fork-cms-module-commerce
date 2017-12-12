<?php

namespace Backend\Modules\Catalog\Domain\SpecificationValue;

use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\Specification\Specification;
use Common\Doctrine\Entity\Meta;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_specification_values")
 * @ORM\Entity(repositoryClass="SpecificationValueRepository")
 * @ORM\HasLifecycleCallbacks
 */
class SpecificationValue
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
     * @var Specification
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\Specification\Specification", inversedBy="specification_values")
     * @ORM\JoinColumn(name="specification_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $specification;

    /**
     * @var Meta
     *
     * @ORM\ManyToOne(targetEntity="Common\Doctrine\Entity\Meta",cascade={"remove", "persist"})
     * @ORM\JoinColumn(name="meta_id", referencedColumnName="id")
     */
    private $meta;

    /**
     * @var Product[]
     *
     * @ORM\ManyToMany(targetEntity="Backend\Modules\Catalog\Domain\Product\Product", mappedBy="specification_values")
     * @ORM\JoinTable(
     *     name="catalog_products_specification_values",
     *     inverseJoinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")},
     *     joinColumns={@ORM\JoinColumn(name="specification_value_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    private $products;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    public $value;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param Product $product
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * @return Specification
     */
    public function getSpecification(): ?Specification
    {
        return $this->specification;
    }

    /**
     * @param Specification $specification
     */
    public function setSpecification(Specification $specification)
    {
        $this->specification = $specification;
    }

    /**
     * @return string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return Meta
     */
    public function getMeta(): ?Meta
    {
        return $this->meta;
    }

    /**
     * @param Meta $meta
     */
    public function setMeta(Meta $meta)
    {
        $this->meta = $meta;
    }

    public function getDataTransferObject(): SpecificationValueDataTransferObject
    {
        return new SpecificationValueDataTransferObject($this);
    }

    public static function fromDataTransferObject(SpecificationValueDataTransferObject $dataTransferObject)
    {
        if ($dataTransferObject->hasExistingSpecificationValue()) {
            $specificationValue = $dataTransferObject->getSpecificationValueEntity();
        } else {
            $specificationValue = new self();
        }

        // Set the values
        $specificationValue->setValue($dataTransferObject->value);
        $specificationValue->setSpecification($dataTransferObject->specification);
        $specificationValue->setMeta($dataTransferObject->meta);

        return $specificationValue;
    }
}

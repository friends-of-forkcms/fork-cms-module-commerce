<?php

namespace Backend\Modules\Catalog\Domain\SpecificationValue;

use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\Specification\Specification;
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
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\Product\Product", inversedBy="specification_values")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $product;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $value;

    /**
     * @return int
     */
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
}

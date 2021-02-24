<?php

namespace Backend\Modules\Commerce\Domain\SpecificationValue;

use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\Specification\Specification;
use Common\Doctrine\Entity\Meta;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="commerce_specification_values")
 * @ORM\Entity(repositoryClass="SpecificationValueRepository")
 * @ORM\HasLifecycleCallbacks
 */
class SpecificationValue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Specification\Specification", inversedBy="specification_values")
     * @ORM\JoinColumn(name="specification_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Specification $specification;

    /**
     * @ORM\ManyToOne(targetEntity="Common\Doctrine\Entity\Meta", cascade={"remove", "persist"})
     * @ORM\JoinColumn(name="meta_id", referencedColumnName="id")
     */
    private ?Meta $meta;

    /**
     * @var Collection|Product[]
     *
     * @ORM\ManyToMany(targetEntity="Backend\Modules\Commerce\Domain\Product\Product", mappedBy="specification_values")
     * @ORM\JoinTable(
     *     name="commerce_products_specification_values",
     *     inverseJoinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *     joinColumns={@ORM\JoinColumn(name="specification_value_id", referencedColumnName="id")}
     * )
     */
    private Collection $products;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public string $value;

    /**
     * @ORM\Column(type="integer", length=11)
     */
    private int $sequence;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

    public function getSpecification(): ?Specification
    {
        return $this->specification;
    }

    public function setSpecification(Specification $specification): void
    {
        $this->specification = $specification;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function setSequence(int $sequence): void
    {
        $this->sequence = $sequence;
    }

    public function getMeta(): ?Meta
    {
        return $this->meta;
    }

    public function setMeta(Meta $meta): void
    {
        $this->meta = $meta;
    }

    public function getDataTransferObject(): SpecificationValueDataTransferObject
    {
        return new SpecificationValueDataTransferObject($this);
    }

    public static function fromDataTransferObject(SpecificationValueDataTransferObject $dataTransferObject): SpecificationValue
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
        $specificationValue->setSequence($dataTransferObject->sequence);

        return $specificationValue;
    }
}

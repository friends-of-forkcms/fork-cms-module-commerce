<?php

namespace Backend\Modules\Commerce\Domain\SpecificationValue;

use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\Specification\Specification;
use Common\Doctrine\Entity\Meta;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="commerce_specification_values")
 * @ORM\Entity(repositoryClass="SpecificationValueRepository")
 */
class SpecificationValue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Specification\Specification", inversedBy="specificationValues")
     * @ORM\JoinColumn(name="specificationId", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Specification $specification;

    /**
     * @ORM\ManyToOne(targetEntity="Common\Doctrine\Entity\Meta", cascade={"remove", "persist"})
     * @ORM\JoinColumn(name="metaId", referencedColumnName="id")
     */
    private ?Meta $meta;

    /**
     * @var Collection|Product[]
     *
     * @ORM\ManyToMany(targetEntity="Backend\Modules\Commerce\Domain\Product\Product", mappedBy="specificationValues")
     * @ORM\JoinTable(
     *     name="commerce_products_specification_values",
     *     inverseJoinColumns={@ORM\JoinColumn(name="productId", referencedColumnName="id")},
     *     joinColumns={@ORM\JoinColumn(name="specificationValueId", referencedColumnName="id")}
     * )
     */
    private Collection $products;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public string $value;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer", length=11)
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

    public function getSequence(): ?int
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

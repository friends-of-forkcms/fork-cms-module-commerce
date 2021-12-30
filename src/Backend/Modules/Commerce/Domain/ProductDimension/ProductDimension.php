<?php

namespace Backend\Modules\Commerce\Domain\ProductDimension;

use Backend\Modules\Commerce\Domain\Product\Product;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Money\Money;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="commerce_product_dimensions")
 * @ORM\Entity(repositoryClass="ProductDimensionRepository")
 */
class ProductDimension
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Product\Product", inversedBy="dimensions")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Product $product;

    /**
     * @ORM\Embedded(class="\Money\Money", columnPrefix="price")
     */
    private Money $price;

    /**
     * @ORM\Column(type="integer")
     */
    private int $width;

    /**
     * @ORM\Column(type="integer")
     */
    private int $height;

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

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public function getPrice(): Money
    {
        return $this->price;
    }

    public function setPrice(Money $price): void
    {
        $this->price = $price;
    }

    /**
     * Get the vat price only.
     */
    public function getVatPrice(): Money
    {
        return $this->getPrice()->multiply($this->product->getVat()->getAsPercentage());
    }

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): void
    {
        $this->width = $width;
    }

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int $height): void
    {
        $this->height = $height;
    }
}

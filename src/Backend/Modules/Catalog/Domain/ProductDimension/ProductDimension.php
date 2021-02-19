<?php

namespace Backend\Modules\Catalog\Domain\ProductDimension;

use Backend\Modules\Catalog\Domain\Product\Product;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="catalog_product_dimensions")
 * @ORM\Entity(repositoryClass="ProductDimensionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductDimension
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
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\Product\Product", inversedBy="dimensions")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $product;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $price;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $width;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $height;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(?int $id)
    {
        $this->id = $id;
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
     * @Assert\NotBlank(message="err.FieldIsRequired")
     *
     * @return float
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price)
    {
        $this->price = $price;
    }

    /**
     * Get the vat price only
     *
     * @return float
     */
    public function getVatPrice()
    {
        return $this->getPrice() * $this->product->getVat()->getAsPercentage();
    }

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     *
     * @return int
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @param int $width
     */
    public function setWidth(int $width)
    {
        $this->width = $width;
    }

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     *
     * @return int
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    public function setHeight(int $height)
    {
        $this->height = $height;
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\ProductDimensionNotification;

use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="commerce_product_dimension_notification")
 * @ORM\Entity(repositoryClass="ProductRepository")
 */
class ProductDimensionNotification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Product\Product", inversedBy="dimensionNotifications")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Product $product;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\ProductOption\ProductOption", inversedBy="dimensionNotifications")
     * @ORM\JoinColumn(name="productOptionId", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?ProductOption $productOption;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private int $width;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private int $height;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $message;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->width = 0;
        $this->height = 0;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

    public function getProductOption(): ?ProductOption
    {
        return $this->productOption;
    }

    public function setProductOption(ProductOption $productOption): void
    {
        $this->productOption = $productOption;
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
    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): void
    {
        $this->height = $height;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}

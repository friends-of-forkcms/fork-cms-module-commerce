<?php

namespace Backend\Modules\Commerce\Domain\ProductDimensionNotification;

use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="commerce_product_dimension_notification")
 * @ORM\Entity(repositoryClass="ProductRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductDimensionNotification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Product\Product", inversedBy="dimension_notifications")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Product $product;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\ProductOption\ProductOption", inversedBy="dimension_notifications")
     * @ORM\JoinColumn(name="product_option_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?ProductOption $product_option;

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
        return $this->product_option;
    }

    public function setProductOption(ProductOption $product_option): void
    {
        $this->product_option = $product_option;
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

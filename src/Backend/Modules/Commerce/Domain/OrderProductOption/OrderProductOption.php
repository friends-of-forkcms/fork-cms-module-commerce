<?php

namespace Backend\Modules\Commerce\Domain\OrderProductOption;

use Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="commerce_order_product_options")
 * @ORM\Entity(repositoryClass="OrderProductOptionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class OrderProductOption
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct", inversedBy="product_options")
     * @ORM\JoinColumn(name="order_product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private OrderProduct $order_product;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $sku;

    /**
     * @ORM\Column(type="string")
     */
    private string $title;

    /**
     * @ORM\Column(type="string")
     */
    private string $value;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private float $price;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private float $total;

    public function getDataTransferObject(): OrderProductOptionDataTransferObject
    {
        return new OrderProductOptionDataTransferObject($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrderProduct(): OrderProduct
    {
        return $this->order_product;
    }

    public function setOrderProduct(OrderProduct $order_product): void
    {
        $this->order_product = $order_product;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): void
    {
        $this->sku = $sku;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function setTotal(float $total): void
    {
        $this->total = $total;
    }
}

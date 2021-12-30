<?php

namespace Backend\Modules\Commerce\Domain\OrderProductOption;

use Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Money\Money;

/**
 * @ORM\Table(name="commerce_order_product_options")
 * @ORM\Entity(repositoryClass="OrderProductOptionRepository")
 */
class OrderProductOption
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct", inversedBy="productOptions")
     * @ORM\JoinColumn(name="orderProductId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private OrderProduct $orderProduct;

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
     * @ORM\Embedded(class="\Money\Money", columnPrefix="price")
     */
    private Money $price;

    /**
     * @ORM\Embedded(class="\Money\Money", columnPrefix="total")
     */
    private Money $total;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdAt;

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
        return $this->orderProduct;
    }

    public function setOrderProduct(OrderProduct $orderProduct): void
    {
        $this->orderProduct = $orderProduct;
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

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function setPrice(Money $price): void
    {
        $this->price = $price;
    }

    public function getTotal(): Money
    {
        return $this->total;
    }

    public function setTotal(Money $total): void
    {
        $this->total = $total;
    }
}

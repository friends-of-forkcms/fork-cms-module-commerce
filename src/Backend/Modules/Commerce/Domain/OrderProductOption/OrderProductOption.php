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
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @var OrderProduct
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct", inversedBy="product_options")
     * @ORM\JoinColumn(name="order_product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $order_product;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $sku;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $value;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $price;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $total;

    public function getDataTransferObject(): OrderProductOptionDataTransferObject
    {
        return new OrderProductOptionDataTransferObject($this);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return OrderProduct
     */
    public function getOrderProduct(): OrderProduct
    {
        return $this->order_product;
    }

    /**
     * @param OrderProduct $order_product
     */
    public function setOrderProduct(OrderProduct $order_product): void
    {
        $this->order_product = $order_product;
    }

    /**
     * @return string
     */
    public function getSku(): ?string
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     */
    public function setSku(?string $sku): void
    {
        $this->sku = $sku;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * @param float $total
     */
    public function setTotal(float $total): void
    {
        $this->total = $total;
    }
}

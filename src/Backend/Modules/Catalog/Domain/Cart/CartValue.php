<?php

namespace Backend\Modules\Catalog\Domain\Cart;

use Backend\Modules\Catalog\Domain\Product\Product;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_cart_values")
 * @ORM\Entity(repositoryClass="CartValueRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CartValue
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
     * @var Cart
     *
     * @ORM\ManyToOne(targetEntity="Cart", inversedBy="values")
     * @ORM\JoinColumn(name="cart_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $cart;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Cart", inversedBy="values")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $product;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", length=11, nullable=true)
     */
    private $quantity;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $total;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="date")
     */
    private $date;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Cart
     */
    public function getCart(): Cart
    {
        return $this->cart;
    }

    /**
     * @param Cart $cart
     */
    public function setCart(Cart $cart)
    {
        $this->cart = $cart;
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
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;
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
    public function setTotal(float $total)
    {
        $this->total = $total;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }

}

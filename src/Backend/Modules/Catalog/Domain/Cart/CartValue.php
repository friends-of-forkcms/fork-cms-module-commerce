<?php

namespace Backend\Modules\Catalog\Domain\Cart;

use Backend\Modules\Catalog\Domain\Product\Product;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @var CartValueOption[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\Cart\CartValueOption", mappedBy="cart_value", cascade={"remove", "persist"})
     */
    private $cart_value_options;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\Product\Product", inversedBy="cart_values")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $product;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", length=11, nullable=true)
     */
    private $quantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $total = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="date")
     */
    private $date;

    /**
     * @var bool
     */
    private $isInStock;

    public function __construct()
    {
        $this->cart_value_options = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): ?int
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
     * @return CartValueOption[]
     */
    public function getCartValueOptions()
    {
        return $this->cart_value_options;
    }

    /**
     * @param CartValueOption[] $cart_value_options
     */
    public function setCartValueOptions(array $cart_value_options): void
    {
        $this->cart_value_options = $cart_value_options;
    }

    /**
     * Add a cart value option
     *
     * @param CartValueOption $cartValueOption
     */
    public function addCartValueOption(CartValueOption $cartValueOption): void
    {
        $this->cart_value_options->add($cartValueOption);
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

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if (!$this->id) {
            $this->date = new \DateTime();
        }
    }

    public function isInStock(): bool
    {
        if ($this->isInStock === null) {
            $inStock = true;

            if (!$this->getProduct()->inStock()) {
                $inStock = false;
            }

            if ($this->getQuantity() > $this->getProduct()->inStock()) {
                $inStock = false;
            }

            $this->isInStock = $inStock;
        }

        return $this->isInStock;
    }
}

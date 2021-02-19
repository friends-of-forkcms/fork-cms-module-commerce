<?php

namespace Backend\Modules\Catalog\Domain\Cart;

use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\ProductDimension\ProductDimension;
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
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\Cart\CartValueOption", mappedBy="cart_value", orphanRemoval=true, cascade={"persist", "remove"})
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
     * @var ProductDimension
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\ProductDimension\ProductDimension", inversedBy="cart_values")
     * @ORM\JoinColumn(name="product_dimension_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $product_dimension;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $width = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $height = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $order_width = 0;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $order_height = 0;

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
     * @var float
     */
    private $price;

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
    public function setCartValueOptions($cart_value_options): void
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
     * @param CartValueOption $cartValueOption
     */
    public function removeCartValueOption(CartValueOption $cartValueOption): void
    {
        if (!$this->cart_value_options->contains($cartValueOption)) {
            return;
        }

        $this->cart_value_options->removeElement($cartValueOption);
    }

    /**
     * @return Product
     */
    public function getProduct(): ?Product
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
     * @return ProductDimension
     */
    public function getProductDimension(): ?ProductDimension
    {
        return $this->product_dimension;
    }

    /**
     * @param ProductDimension $product_dimension
     */
    public function setProductDimension(ProductDimension $product_dimension): void
    {
        $this->product_dimension = $product_dimension;
    }

    /**
     * @return float
     */
    public function getWidth(): ?float
    {
        return $this->width;
    }

    /**
     * @param float $width
     */
    public function setWidth(float $width): void
    {
        $this->width = $width;
    }

    /**
     * @return float
     */
    public function getHeight(): ?float
    {
        return $this->height;
    }

    /**
     * @param float $height
     */
    public function setHeight(float $height): void
    {
        $this->height = $height;
    }

    /**
     * @return float
     */
    public function getOrderWidth(): ?float
    {
        return $this->order_width;
    }

    /**
     * @param float $order_width
     */
    public function setOrderWidth(float $order_width): void
    {
        $this->order_width = $order_width;
    }

    /**
     * @return float
     */
    public function getOrderHeight(): ?float
    {
        return $this->order_height;
    }

    /**
     * @param float $order_height
     */
    public function setOrderHeight(float $order_height): void
    {
        $this->order_height = $order_height;
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

    /**
     * Get the price for the product
     *
     * @return float|null
     */
    public function getPrice(): float
    {
        if ($this->price) {
            return $this->price;
        }

        if ($this->product->usesDimensions()) {
            $this->price = $this->product_dimension->getPrice();
        } else {
            $this->price = $this->product->getActivePrice(false);
        }

        foreach ($this->getCartValueOptions() as $option) {
            if ($option->isImpactTypeAdd()) {
                $this->price += $option->getPrice();
            } else {
                $this->price -= $option->getPrice();
            }
        }

        return $this->price;
    }

    /**
     * Get the vat price of a single product in this row
     *
     * @return float
     */
    public function getVatPrice(): float
    {
        if ($this->product->usesDimensions()) {
            return $this->product_dimension->getVatPrice();
        }

        return $this->product->getVatPrice();
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\Cart;

use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\ProductDimension\ProductDimension;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="commerce_cart_values")
 * @ORM\Entity(repositoryClass="CartValueRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CartValue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Cart", inversedBy="values")
     * @ORM\JoinColumn(name="cart_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private Cart $cart;

    /**
     * @var Collection|CartValueOption[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\Cart\CartValueOption", mappedBy="cart_value", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private Collection $cart_value_options;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Product\Product", inversedBy="cart_values")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private ?Product $product;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\ProductDimension\ProductDimension", inversedBy="cart_values")
     * @ORM\JoinColumn(name="product_dimension_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private ?ProductDimension $product_dimension;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private int $width = 0;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private int $height = 0;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private int $order_width = 0;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private int $order_height = 0;

    /**
     * @ORM\Column(type="integer", length=11, nullable=true)
     */
    private int $quantity = 0;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private int $total = 0;

    /**
     * @ORM\Column(type="datetime", name="date")
     */
    private DateTimeInterface $date;

    private ?float $price;
    private bool $isInStock;

    public function __construct()
    {
        $this->cart_value_options = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function setCart(Cart $cart): void
    {
        $this->cart = $cart;
    }

    /**
     * @return Collection|CartValueOption[]
     */
    public function getCartValueOptions(): Collection
    {
        return $this->cart_value_options;
    }

    /**
     * @param Collection|CartValueOption[] $cart_value_options
     */
    public function setCartValueOptions(Collection $cart_value_options): void
    {
        $this->cart_value_options = $cart_value_options;
    }

    public function addCartValueOption(CartValueOption $cartValueOption): void
    {
        $this->cart_value_options->add($cartValueOption);
    }

    public function removeCartValueOption(CartValueOption $cartValueOption): void
    {
        if (!$this->cart_value_options->contains($cartValueOption)) {
            return;
        }

        $this->cart_value_options->removeElement($cartValueOption);
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

    public function getProductDimension(): ?ProductDimension
    {
        return $this->product_dimension;
    }

    public function setProductDimension(ProductDimension $product_dimension): void
    {
        $this->product_dimension = $product_dimension;
    }

    public function getWidth(): ?float
    {
        return $this->width;
    }

    public function setWidth(float $width): void
    {
        $this->width = $width;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setHeight(float $height): void
    {
        $this->height = $height;
    }

    public function getOrderWidth(): ?float
    {
        return $this->order_width;
    }

    public function setOrderWidth(float $order_width): void
    {
        $this->order_width = $order_width;
    }

    public function getOrderHeight(): ?float
    {
        return $this->order_height;
    }

    public function setOrderHeight(float $order_height): void
    {
        $this->order_height = $order_height;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function setTotal(float $total): void
    {
        $this->total = $total;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): void
    {
        $this->date = $date;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        if (!isset($this->id)) {
            $this->date = new DateTime();
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
     * Get the vat price of a single product in this row.
     */
    public function getVatPrice(): float
    {
        if ($this->product->usesDimensions()) {
            return $this->product_dimension->getVatPrice();
        }

        return $this->product->getVatPrice();
    }
}

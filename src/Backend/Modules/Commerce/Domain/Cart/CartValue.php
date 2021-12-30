<?php

namespace Backend\Modules\Commerce\Domain\Cart;

use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\ProductDimension\ProductDimension;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Money\Money;

/**
 * @ORM\Table(name="commerce_cart_values")
 * @ORM\Entity(repositoryClass="CartValueRepository")
 */
class CartValue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Cart", inversedBy="values")
     * @ORM\JoinColumn(name="cartId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private Cart $cart;

    /**
     * @var Collection<int, CartValueOption>
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\Cart\CartValueOption", mappedBy="cartValue", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private Collection $cartValueOptions;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Product\Product", inversedBy="cartValues")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private ?Product $product;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\ProductDimension\ProductDimension")
     * @ORM\JoinColumn(name="productDimensionId", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private ?ProductDimension $productDimension;

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
    private int $orderWidth = 0;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private int $orderHeight = 0;

    /**
     * @ORM\Column(type="integer", length=11, nullable=true)
     */
    private int $quantity = 0;

    /**
     * @ORM\Embedded(class="\Money\Money", columnPrefix="total")
     */
    private Money $total;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdAt;

    private ?Money $price;
    private bool $isInStock;

    public function __construct()
    {
        $this->cartValueOptions = new ArrayCollection();
        $this->total = Money::EUR(0);
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
     * @return Collection<int, CartValueOption>|CartValueOption[]
     */
    public function getCartValueOptions(): Collection
    {
        return $this->cartValueOptions;
    }

    public function addCartValueOption(CartValueOption $cartValueOption): void
    {
        $this->cartValueOptions->add($cartValueOption);
    }

    public function removeCartValueOption(CartValueOption $cartValueOption): void
    {
        if (!$this->cartValueOptions->contains($cartValueOption)) {
            return;
        }

        $this->cartValueOptions->removeElement($cartValueOption);
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
        return $this->productDimension;
    }

    public function setProductDimension(ProductDimension $productDimension): void
    {
        $this->productDimension = $productDimension;
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
        return $this->orderWidth;
    }

    public function setOrderWidth(float $orderWidth): void
    {
        $this->orderWidth = $orderWidth;
    }

    public function getOrderHeight(): ?float
    {
        return $this->orderHeight;
    }

    public function setOrderHeight(float $orderHeight): void
    {
        $this->orderHeight = $orderHeight;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getTotal(): Money
    {
        return $this->total;
    }

    public function setTotal(Money $total): void
    {
        $this->total = $total;
    }

    public function isInStock(): bool
    {
        if (!isset($this->isInStock)) {
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

    public function getPrice(): Money
    {
        if (isset($this->price)) {
            return $this->price;
        }

        if ($this->product->usesDimensions()) {
            $this->price = $this->productDimension->getPrice();
        } else {
            $this->price = $this->product->getActivePrice(false);
        }

        foreach ($this->getCartValueOptions() as $option) {
            if ($option->isImpactTypeAdd()) {
                $this->price = $this->price->add($option->getPrice());
            } elseif ($option->isImpactTypeSubtract()) {
                $this->price = $this->price->subtract($option->getPrice());
            }
        }

        return $this->price;
    }

    /**
     * Get the vat price of a single product in this row.
     */
    public function getVatPrice(): Money
    {
        if ($this->product->usesDimensions()) {
            return $this->productDimension->getVatPrice();
        }

        return $this->product->getVatPrice();
    }
}

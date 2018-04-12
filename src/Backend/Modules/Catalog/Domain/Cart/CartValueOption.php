<?php

namespace Backend\Modules\Catalog\Domain\Cart;

use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_cart_value_options")
 * @ORM\Entity(repositoryClass="CartValueOptionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CartValueOption
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
     * @var CartValue
     *
     * @ORM\ManyToOne(targetEntity="CartValue", inversedBy="cart_value_options")
     * @ORM\JoinColumn(name="cart_value_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $cart_value;

    /**
     * @var ProductOptionValue
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue")
     * @ORM\JoinColumn(name="product_option_value_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $product_option_value;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return CartValue
     */
    public function getCartValue(): CartValue
    {
        return $this->cart_value;
    }

    /**
     * @param CartValue $cart_value
     */
    public function setCartValue(CartValue $cart_value): void
    {
        $this->cart_value = $cart_value;
    }

    /**
     * @return ProductOptionValue
     */
    public function getProductOptionValue(): ProductOptionValue
    {
        return $this->product_option_value;
    }

    /**
     * @param ProductOptionValue $product_option_value
     */
    public function setProductOptionValue(ProductOptionValue $product_option_value): void
    {
        $this->product_option_value = $product_option_value;
    }

    public function getTotal(): float
    {
        return $this->cart_value->getQuantity() * $this->getProductOptionValue()->getPrice();
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\Cart;

use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="commerce_cart_value_options")
 * @ORM\Entity(repositoryClass="CartValueOptionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CartValueOption
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="CartValue", inversedBy="cart_value_options")
     * @ORM\JoinColumn(name="cart_value_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private CartValue $cart_value;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\ProductOption\ProductOption", inversedBy="cart_value_options")
     * @ORM\JoinColumn(name="product_option_id", referencedColumnName="id", nullable=true, onDelete="set null")
     */
    private ?ProductOption $product_option;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue")
     * @ORM\JoinColumn(name="product_option_value_id", referencedColumnName="id", nullable=true)
     */
    private ?ProductOptionValue $product_option_value;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Vat\Vat")
     * @ORM\JoinColumn(name="vat_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Vat $vat;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private float $price;

    /**
     * @ORM\Column(type="integer")
     */
    private int $impact_type;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private float $vat_price;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $value;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCartValue(): CartValue
    {
        return $this->cart_value;
    }

    public function setCartValue(CartValue $cart_value): void
    {
        $this->cart_value = $cart_value;
    }

    public function getProductOption(): ProductOption
    {
        return $this->product_option;
    }

    public function setProductOption(ProductOption $product_option): void
    {
        $this->product_option = $product_option;
    }

    public function getProductOptionValue(): ?ProductOptionValue
    {
        return $this->product_option_value;
    }

    public function setProductOptionValue(ProductOptionValue $product_option_value): void
    {
        $this->product_option_value = $product_option_value;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getImpactType(): int
    {
        if (!$this->impact_type) {
            $this->impact_type = ProductOptionValue::IMPACT_TYPE_ADD;
        }

        return $this->impact_type;
    }

    public function setImpactType(int $impact_type): void
    {
        $this->impact_type = $impact_type;
    }

    public function isImpactTypeAdd(): bool
    {
        return $this->getImpactType() === ProductOptionValue::IMPACT_TYPE_ADD;
    }

    public function isImpactTypeSubtract(): bool
    {
        return $this->getImpactType() === ProductOptionValue::IMPACT_TYPE_SUBTRACT;
    }

    public function getVat(): Vat
    {
        return $this->vat;
    }

    public function setVat(Vat $vat): void
    {
        $this->vat = $vat;
    }

    public function getVatPrice(): float
    {
        return $this->vat_price;
    }

    public function setVatPrice(float $vat_price): void
    {
        $this->vat_price = $vat_price;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getTotal(): float
    {
        return $this->cart_value->getQuantity() * $this->getPrice();
    }
}

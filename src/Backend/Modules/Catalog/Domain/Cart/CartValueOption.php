<?php

namespace Backend\Modules\Catalog\Domain\Cart;

use Backend\Modules\Catalog\Domain\ProductOption\ProductOption;
use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue;
use Backend\Modules\Catalog\Domain\Vat\Vat;
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
     * @var ProductOption
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\ProductOption\ProductOption", inversedBy="cart_value_options")
     * @ORM\JoinColumn(name="product_option_id", referencedColumnName="id", nullable=true, onDelete="set null")
     */
    private $product_option;

    /**
     * @var ProductOptionValue
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue")
     * @ORM\JoinColumn(name="product_option_value_id", referencedColumnName="id", nullable=true)
     */
    private $product_option_value;

    /**
     * @var Vat
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\Vat\Vat")
     * @ORM\JoinColumn(name="vat_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $vat;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $price;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $impact_type;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $vat_price;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $value;

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
     * @return ProductOption
     */
    public function getProductOption(): ProductOption
    {
        return $this->product_option;
    }

    /**
     * @param ProductOption $product_option
     */
    public function setProductOption(ProductOption $product_option): void
    {
        $this->product_option = $product_option;
    }

    /**
     * @return ProductOptionValue|null
     */
    public function getProductOptionValue(): ?ProductOptionValue
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

    /**
     * @return float
     */
    public function getPrice(): ?float
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
     * @return int
     */
    public function getImpactType(): int
    {
        if (!$this->impact_type) {
            $this->impact_type = ProductOptionValue::IMPACT_TYPE_ADD;
        }

        return $this->impact_type;
    }

    /**
     * @param int $impact_type
     */
    public function setImpactType(int $impact_type): void
    {
        $this->impact_type = $impact_type;
    }

    /**
     * @return bool
     */
    public function isImpactTypeAdd(): bool
    {
        return $this->getImpactType() == ProductOptionValue::IMPACT_TYPE_ADD;
    }

    /**
     * @return bool
     */
    public function isImpactTypeSubtract(): bool
    {
        return $this->getImpactType() == ProductOptionValue::IMPACT_TYPE_SUBTRACT;
    }

    /**
     * @return Vat
     */
    public function getVat(): Vat
    {
        return $this->vat;
    }

    /**
     * @param Vat $vat
     */
    public function setVat(Vat $vat): void
    {
        $this->vat = $vat;
    }

    /**
     * @return float
     */
    public function getVatPrice(): float
    {
        return $this->vat_price;
    }

    /**
     * @param float $vat_price
     */
    public function setVatPrice(float $vat_price): void
    {
        $this->vat_price = $vat_price;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getValue(): ?string
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

    public function getTotal(): float
    {
        return $this->cart_value->getQuantity() * $this->getPrice();
    }
}

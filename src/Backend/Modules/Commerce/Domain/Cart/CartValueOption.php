<?php

namespace Backend\Modules\Commerce\Domain\Cart;

use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Money\Money;

/**
 * @ORM\Table(name="commerce_cart_value_options")
 * @ORM\Entity(repositoryClass="CartValueOptionRepository")
 */
class CartValueOption
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="CartValue", inversedBy="cartValueOptions")
     * @ORM\JoinColumn(name="cartValueId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private CartValue $cartValue;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\ProductOption\ProductOption", inversedBy="cartValueOptions")
     * @ORM\JoinColumn(name="productOptionId", referencedColumnName="id", nullable=true, onDelete="set null")
     */
    private ?ProductOption $productOption;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue")
     * @ORM\JoinColumn(name="productOptionValueId", referencedColumnName="id", nullable=true)
     */
    private ?ProductOptionValue $productOptionValue;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Vat\Vat")
     * @ORM\JoinColumn(name="vatId", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Vat $vat;

    /**
     * @ORM\Embedded(class="\Money\Money", columnPrefix="price")
     */
    private Money $price;

    /**
     * @ORM\Column(type="integer")
     */
    private int $impactType;

    /**
     * @ORM\Embedded(class="\Money\Money", columnPrefix="vatPrice")
     */
    private Money $vatPrice;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $value;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCartValue(): CartValue
    {
        return $this->cartValue;
    }

    public function setCartValue(CartValue $cartValue): void
    {
        $this->cartValue = $cartValue;
    }

    public function getProductOption(): ProductOption
    {
        return $this->productOption;
    }

    public function setProductOption(ProductOption $productOption): void
    {
        $this->productOption = $productOption;
    }

    public function getProductOptionValue(): ?ProductOptionValue
    {
        return $this->productOptionValue;
    }

    public function setProductOptionValue(ProductOptionValue $productOptionValue): void
    {
        $this->productOptionValue = $productOptionValue;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function setPrice(Money $price): void
    {
        $this->price = $price;
    }

    public function getImpactType(): int
    {
        if (!$this->impactType) {
            $this->impactType = ProductOptionValue::IMPACT_TYPE_ADD;
        }

        return $this->impactType;
    }

    public function setImpactType(int $impactType): void
    {
        $this->impactType = $impactType;
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

    public function getVatPrice(): Money
    {
        return $this->vatPrice;
    }

    public function setVatPrice(Money $vatPrice): void
    {
        $this->vatPrice = $vatPrice;
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

    public function getTotal(): Money
    {
        return $this->getPrice()->multiply($this->cartValue->getQuantity());
    }
}

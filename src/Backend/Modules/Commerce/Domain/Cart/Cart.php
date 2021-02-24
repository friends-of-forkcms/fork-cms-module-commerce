<?php

namespace Backend\Modules\Commerce\Domain\Cart;

use Backend\Core\Engine\Model;
use Backend\Modules\Commerce\Domain\Account\Account;
use Backend\Modules\Commerce\Domain\CartRule\CartRule;
use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Commerce\Domain\Vat\VatRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Frontend\Core\Language\Locale;

/**
 * @ORM\Table(name="commerce_carts")
 * @ORM\Entity(repositoryClass="CartRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Cart
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Account\Account", inversedBy="carts")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?Account $account;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress")
     * @ORM\JoinColumn(name="shipment_address_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?OrderAddress $shipment_address;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress")
     * @ORM\JoinColumn(name="invoice_address_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?OrderAddress $invoice_address;

    /**
     * @var Collection|CartValue[]
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\Cart\CartValue", mappedBy="cart", cascade={"remove", "persist"})
     */
    private Collection $values;

    /**
     * @var Collection|CartRule[]
     *
     * @ORM\ManyToMany(targetEntity="Backend\Modules\Commerce\Domain\CartRule\CartRule")
     * @ORM\JoinTable(name="commerce_cart_cart_rules",
     *     joinColumns={@ORM\JoinColumn(name="cart_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="cart_rule_id", referencedColumnName="id")}
     * )
     */
    private Collection $cart_rules;

    /**
     * @ORM\Column(type="integer")
     */
    private int $total_quantity = 0;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $ip;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $session_id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $shipment_method;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private ?array $shipment_method_data;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $payment_method;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private ?array $payment_method_data;

    /**
     * @ORM\Column(type="datetime", name="created_on")
     */
    private DateTimeInterface $createdOn;

    /**
     * @ORM\Column(type="datetime", name="edited_on")
     */
    private DateTimeInterface $editedOn;

    /**
     * @ORM\OneToOne(targetEntity="Backend\Modules\Commerce\Domain\Order\Order", mappedBy="cart")
     */
    private ?Order $order;

    private float $vatTotals;
    private int $total;
    private int $subTotal;
    private array $vats = [];
    private bool $allProductsInStock;
    private int $totalWeight;
    private array $cartRuleTotals = [];

    public function __construct()
    {
        $this->values = new ArrayCollection();
        $this->cart_rules = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    public function getShipmentAddress(): ?OrderAddress
    {
        return $this->shipment_address;
    }

    public function setShipmentAddress(OrderAddress $shipment_address): void
    {
        $this->shipment_address = $shipment_address;
    }

    public function getInvoiceAddress(): ?OrderAddress
    {
        return $this->invoice_address;
    }

    public function setInvoiceAddress(?OrderAddress $invoice_address): void
    {
        $this->invoice_address = $invoice_address;
    }

    /**
     * @return Collection|CartValue[]
     */
    public function getValues(): Collection
    {
        return $this->values;
    }

    public function addValue(CartValue $value): void
    {
        $this->values->add($value);

        // Recalculate
        $this->calculateTotalQuantity();
        $this->calculateTotals();
    }

    public function removeValue(CartValue $value): void
    {
        $this->values->removeElement($value);

        // Recalculate
        $this->calculateTotalQuantity();
        $this->calculateTotals();
    }

    /**
     * @return Collection|CartRule[]
     */
    public function getCartRules(): Collection
    {
        return $this->cart_rules;
    }

    public function addCartRule(CartRule $cartRule): void
    {
        $this->cart_rules->add($cartRule);

        // Recalculate
        $this->calculateTotalQuantity();
        $this->calculateTotals();
    }

    public function removeCartRule(CartRule $cartRule): void
    {
        $this->cart_rules->removeElement($cartRule);

        // Recalculate
        $this->calculateTotalQuantity();
        $this->calculateTotals();
    }

    public function getTotalQuantity(): int
    {
        return $this->total_quantity;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    public function getSessionId(): string
    {
        return $this->session_id;
    }

    public function setSessionId(string $session_id): void
    {
        $this->session_id = $session_id;
    }

    public function getShipmentMethod(): ?string
    {
        return $this->shipment_method;
    }

    public function setShipmentMethod(?string $shipment_method): void
    {
        $this->shipment_method = $shipment_method;
    }

    public function getShipmentMethodData(): ?array
    {
        return $this->shipment_method_data;
    }

    public function setShipmentMethodData(?array $shipment_method_data): void
    {
        $this->shipment_method_data = $shipment_method_data;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->payment_method;
    }

    public function setPaymentMethod(?string $payment_method): void
    {
        $this->payment_method = $payment_method;
    }

    public function getPaymentMethodData(): ?array
    {
        return $this->payment_method_data;
    }

    public function setPaymentMethodData(?array $payment_method_data): void
    {
        $this->payment_method_data = $payment_method_data;
    }

    public function getCreatedOn(): DateTimeInterface
    {
        return $this->createdOn;
    }

    public function setCreatedOn(DateTimeInterface $createdOn): void
    {
        $this->createdOn = $createdOn;
    }

    public function getEditedOn(): DateTimeInterface
    {
        return $this->editedOn;
    }

    public function setEditedOn(DateTimeInterface $editedOn): void
    {
        $this->editedOn = $editedOn;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        $this->editedOn = new DateTime();

        if (!$this->id) {
            $this->createdOn = $this->editedOn;
        }
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function getTotal(): float
    {
        if (!$this->total) {
            $this->calculateTotals();
        }

        return $this->total;
    }

    public function getSubTotal(): float
    {
        if (!$this->subTotal) {
            $this->calculateTotals();
        }

        return $this->subTotal;
    }

    public function getVats(): array
    {
        if (!$this->vats) {
            $this->calculateTotals();
        }

        return $this->vats;
    }

    public function getVatTotals(): ?float
    {
        if (!$this->vatTotals) {
            $this->calculateTotals();

            foreach ($this->vats as $vat) {
                $this->vatTotals += $vat['total'];
            }
        }

        return $this->vatTotals;
    }

    public function getTotalWeight(): float
    {
        if ($this->totalWeight === null) {
            $this->calculateTotals();
        }

        return $this->totalWeight;
    }

    /**
     * Calculate the cart totals.
     */
    public function calculateTotals(): void
    {
        // Reset the values
        $this->subTotal = 0;
        $this->total = 0;
        $this->vats = [];
        $this->totalWeight = 0;

        // Store new values
        foreach ($this->values as $value) {
            // Update the product totals
            $product = $value->getProduct();
            $vat = $product->getVat();
            $vatPrice = $value->getVatPrice() * $value->getQuantity();

            if ($product->getWeight() !== null) {
                $this->totalWeight += $product->getWeight() * $value->getQuantity();
            }

            $this->subTotal += $value->getTotal();
            $this->total += $value->getTotal() + $vatPrice;
            $this->addVat($vat, $vatPrice);

            // Update the product option totals
            foreach ($value->getCartValueOptions() as $valueOption) {
                $vat = $valueOption->getVat();

                if (!array_key_exists($vat->getId(), $this->vats)) {
                    $this->vats[$vat->getId()] = [
                        'title' => $vat->getTitle(),
                        'total' => 0,
                    ];
                }

                $vatPrice = $valueOption->getVatPrice() * $value->getQuantity();

                if ($valueOption->isImpactTypeAdd()) {
                    $this->addVat($vat, $vatPrice);
                    $this->total += $vatPrice;
                } else {
                    $this->addVat($vat, $vatPrice, true);
                    $this->total -= $vatPrice;
                }
            }
        }

        $this->calculateCartRules();

        // Store the shipment data
        if ($this->shipment_method) {
            $shipmentMethodData = $this->getShipmentMethodData();
            $this->total += $shipmentMethodData['price'] + $shipmentMethodData['vat']['price'];
            $this->addVat($shipmentMethodData['vat']['id'], $shipmentMethodData['vat']['price']);
        }
    }

    private function addVat($vat, ?float $price, $subtractVat = false): void
    {
        if (is_int($vat)) {
            /** @var VatRepository $vatRepository */
            $vatRepository = Model::get('commerce.repository.vat');
            $vat = $vatRepository->findOneByIdAndLocale($vat, Locale::frontendLanguage());
        }

        if (!array_key_exists($vat->getId(), $this->vats)) {
            $this->vats[$vat->getId()] = [
                'title' => $vat->getTitle(),
                'total' => 0,
            ];
        }

        if ($subtractVat) {
            $this->vats[$vat->getId()]['total'] -= $price;
        } else {
            $this->vats[$vat->getId()]['total'] += $price;
        }
    }

    private function calculateTotalQuantity(): void
    {
        $totalQuantity = 0;

        foreach ($this->values as $value) {
            $totalQuantity += $value->getQuantity();
        }

        $this->total_quantity = $totalQuantity;
    }

    /**
     * Check if all the products are in stock.
     */
    public function isProductsInStock(): bool
    {
        if ($this->allProductsInStock === null) {
            $inStock = true;

            foreach ($this->getValues() as $value) {
                if (!$value->isInStock()) {
                    $inStock = false;
                }
            }

            $this->allProductsInStock = $inStock;
        }

        return $this->allProductsInStock;
    }

    private function calculateCartRules(): void
    {
        foreach ($this->cart_rules as $cartRule) {
            if ($cartRule->getReductionPercentage()) {
                $total = $this->applyPercentageDiscount($cartRule->getReductionPercentage() / 100);

                $this->setCartRuleTotal($cartRule, $total);
            }

            if ($cartRule->getReductionAmount()) {
                $total = $this->applyPercentageDiscount($cartRule->getReductionAmount() / $this->total);

                $this->setCartRuleTotal($cartRule, $total);
            }
        }
    }

    private function applyPercentageDiscount($percentage)
    {
        $this->subTotal -= $this->subTotal * $percentage;

        foreach ($this->vats as $key => $vat) {
            $this->vats[$key]['total'] -= $vat['total'] * $percentage;
        }

        $total = $this->total * $percentage;

        $this->total -= $total;

        return $total;
    }

    public function getCartRuleTotal(CartRule $cartRule): ?float
    {
        return $this->cartRuleTotals[$cartRule->getId()] ?? null;
    }

    private function setCartRuleTotal(CartRule $cartRule, float $total): void
    {
        $this->cartRuleTotals[$cartRule->getId()] = $total;
    }
}

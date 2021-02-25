<?php

namespace Backend\Modules\Commerce\Domain\Cart;

use Backend\Core\Engine\Model;
use Backend\Modules\Commerce\Domain\Account\Account;
use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\CartRule\CartRule;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\Commerce\Domain\Vat\VatRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Account\Account", inversedBy="carts")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $account;

    /**
     * @var OrderAddress
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress")
     * @ORM\JoinColumn(name="shipment_address_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $shipment_address;

    /**
     * @var OrderAddress
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress")
     * @ORM\JoinColumn(name="invoice_address_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $invoice_address;

    /**
     * @var CartValue[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\Cart\CartValue", mappedBy="cart", cascade={"remove", "persist"})
     */
    private $values;

    /**
     * @var CartRule[]
     *
     * @ORM\ManyToMany(targetEntity="Backend\Modules\Commerce\Domain\CartRule\CartRule")
     * @ORM\JoinTable(name="commerce_cart_cart_rules",
     *      joinColumns={@ORM\JoinColumn(name="cart_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="cart_rule_id", referencedColumnName="id")}
     * )
     */
    private $cart_rules;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $total_quantity = 0;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $ip;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $session_id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $shipment_method;

    /**
     * @var array
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $shipment_method_data;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $payment_method;

    /**
     * @var array
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $payment_method_data;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", name="created_on")
     */
    private $createdOn;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", name="edited_on")
     */
    private $editedOn;

    /**
     * @var Order
     *
     * @ORM\OneToOne(targetEntity="Backend\Modules\Commerce\Domain\Order\Order", mappedBy="cart")
     */
    private $order;

    /**
     * @var float
     */
    private $vatTotals;

    /**
     * @var float
     */
    private $total;

    /**
     * @var float
     */
    private $subTotal;

    /**
     * @var array
     */
    private $vats = [];

    /**
     * @var bool
     */
    private $allProductsInStock;

    /**
     * @var double
     */
    private $totalWeight;

    /**
     * @var array
     */
    private $cartRuleTotals = [];

    /**
     * Cart constructor.
     */
    public function __construct()
    {
        $this->values = new ArrayCollection();
        $this->cart_rules = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return Account
     */
    public function getAccount(): ?Account
    {
        return $this->account;
    }

    /**
     * @param Account $account
     */
    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    /**
     * @return OrderAddress
     */
    public function getShipmentAddress(): ?OrderAddress
    {
        return $this->shipment_address;
    }

    /**
     * @param OrderAddress $shipment_address
     */
    public function setShipmentAddress(OrderAddress $shipment_address): void
    {
        $this->shipment_address = $shipment_address;
    }

    /**
     * @return OrderAddress
     */
    public function getInvoiceAddress(): ?OrderAddress
    {
        return $this->invoice_address;
    }

    /**
     * @param OrderAddress $invoice_address
     */
    public function setInvoiceAddress(?OrderAddress $invoice_address): void
    {
        $this->invoice_address = $invoice_address;
    }

    /**
     * @return CartValue[]
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param CartValue $value
     *
     * @return void
     */
    public function addValue(CartValue $value): void
    {
        $this->values->add($value);

        // Recalculate on add
        $this->calculateTotalQuantity();
        $this->calculateTotals();
    }

    /**
     * @param CartValue $value
     *
     * @return void
     */
    public function removeValue(CartValue $value): void
    {
        $this->values->removeElement($value);

        $this->calculateTotalQuantity();
        $this->calculateTotals();
    }

    /**
     * @return CartRule[]
     */
    public function getCartRules()
    {
        return $this->cart_rules;
    }

    /**
     * @param CartRule $cartRule
     *
     * @return void
     */
    public function addCartRule(CartRule $cartRule): void
    {
        $this->cart_rules->add($cartRule);

        $this->calculateTotalQuantity();
        $this->calculateTotals();
    }

    /**
     * @param CartRule $cartRule
     *
     * @return void
     */
    public function removeCartRule(CartRule $cartRule): void
    {
        $this->cart_rules->removeElement($cartRule);

        $this->calculateTotalQuantity();
        $this->calculateTotals();
    }

    /**
     * @return int
     */
    public function getTotalQuantity(): int
    {
        return $this->total_quantity;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp(string $ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->session_id;
    }

    /**
     * @param string $session_id
     */
    public function setSessionId(string $session_id)
    {
        $this->session_id = $session_id;
    }

    /**
     * @return string
     */
    public function getShipmentMethod(): ?string
    {
        return $this->shipment_method;
    }

    /**
     * @param string $shipment_method
     */
    public function setShipmentMethod(?string $shipment_method): void
    {
        $this->shipment_method = $shipment_method;
    }

    /**
     * @return array
     */
    public function getShipmentMethodData(): ?array
    {
        return $this->shipment_method_data;
    }

    /**
     * @param array $shipment_method_data
     */
    public function setShipmentMethodData(?array $shipment_method_data): void
    {
        $this->shipment_method_data = $shipment_method_data;
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): ?string
    {
        return $this->payment_method;
    }

    /**
     * @param string $payment_method
     */
    public function setPaymentMethod(?string $payment_method): void
    {
        $this->payment_method = $payment_method;
    }

    /**
     * @return array
     */
    public function getPaymentMethodData(): ?array
    {
        return $this->payment_method_data;
    }

    /**
     * @param array $payment_method_data
     */
    public function setPaymentMethodData(?array $payment_method_data): void
    {
        $this->payment_method_data = $payment_method_data;
    }

    /**
     * @return DateTime
     */
    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }

    /**
     * @param DateTime $createdOn
     */
    public function setCreatedOn(DateTime $createdOn)
    {
        $this->createdOn = $createdOn;
    }

    /**
     * @return DateTime
     */
    public function getEditedOn(): DateTime
    {
        return $this->editedOn;
    }

    /**
     * @param DateTime $editedOn
     */
    public function setEditedOn(DateTime $editedOn)
    {
        $this->editedOn = $editedOn;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->editedOn = new DateTime();

        if (!$this->id) {
            $this->createdOn = $this->editedOn;
        }
    }

    /**
     * @return Order|null
     */
    public function getOrder(): ?Order
    {
        return $this->order;
    }

    /**
     * Get the total cart value
     *
     * @return float
     */
    public function getTotal(): float
    {
        if (!$this->total) {
            $this->calculateTotals();
        }

        return $this->total;
    }

    /**
     * @return float
     */
    public function getSubTotal(): float
    {
        if (!$this->subTotal) {
            $this->calculateTotals();
        }

        return $this->subTotal;
    }

    /**
     * @return array
     */
    public function getVats(): array
    {
        if (!$this->vats) {
            $this->calculateTotals();
        }

        return $this->vats;
    }

    /**
     * @return float
     */
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

    /**
     * @return float|null
     */
    public function getTotalWeight(): float
    {
        if ($this->totalWeight === null) {
            $this->calculateTotals();
        }

        return $this->totalWeight;
    }

    /**
     * Calculate the cart totals
     *
     * @return void
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
                        'total' => 0
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

    /**
     * @param Vat|int $vat
     * @param float|null $price
     * @param bool $subtractVat
     */
    private function addVat($vat, ?float $price, $subtractVat = false)
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

    /**
     * Calculate the total quantity
     *
     * @return void
     */
    private function calculateTotalQuantity(): void
    {
        $totalQuantity = 0;

        foreach ($this->values as $value) {
            $totalQuantity += $value->getQuantity();
        }

        $this->total_quantity = $totalQuantity;
    }

    /**
     * Check if all the products are in stock
     *
     * @return boolean
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

    private function calculateCartRules()
    {
        foreach ($this->cart_rules as $cartRule) {
            if ($cartRule->getReductionPercentage()) {
                $total = $this->applyPercentageDiscount($cartRule->getReductionPercentage()/100);

                $this->setCartRuleTotal($cartRule, $total);
            }

            if ($cartRule->getReductionAmount()) {
                $total = $this->applyPercentageDiscount($cartRule->getReductionAmount()/$this->total);

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
        if (!array_key_exists($cartRule->getId(), $this->cartRuleTotals)) {
            return null;
        }

        return $this->cartRuleTotals[$cartRule->getId()];
    }

    private function setCartRuleTotal(CartRule $cartRule, float $total): void
    {
        $this->cartRuleTotals[$cartRule->getId()] = $total;
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\Cart;

use Backend\Core\Engine\Model;
use Backend\Modules\Commerce\Domain\Account\Account;
use Backend\Modules\Commerce\Domain\CartRule\CartRule;
use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Commerce\Domain\Vat\VatRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Frontend\Core\Language\Locale;
use Gedmo\Mapping\Annotation as Gedmo;
use JsonSerializable;
use Money\Currency;
use Money\Money;
use Tbbc\MoneyBundle\Formatter\MoneyFormatter;

/**
 * @ORM\Table(name="commerce_carts")
 * @ORM\Entity(repositoryClass="CartRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Cart implements JsonSerializable
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
     * @var Collection<int, CartValue>
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\Cart\CartValue", mappedBy="cart", cascade={"remove", "persist"})
     */
    private Collection $values;

    /**
     * @var Collection<int, CartRule>|CartRule[]
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
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="created_on", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdOn;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", name="edited_on", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $editedOn;

    /**
     * @ORM\OneToOne(targetEntity="Backend\Modules\Commerce\Domain\Order\Order", mappedBy="cart")
     */
    private ?Order $order;

    private Money $vatTotal;
    private Money $total;
    private Money $subTotal;
    private array $vats = [];
    private bool $allProductsInStock;
    private float $totalWeight;
    private array $cartRuleTotals = [];
    private MoneyFormatter $moneyFormatter;

    public function __construct()
    {
        $this->values = new ArrayCollection();
        $this->cart_rules = new ArrayCollection();
        $this->moneyFormatter = new MoneyFormatter();
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
     * @return Collection<int, CartValue>
     */
    public function getValues(): Collection
    {
        return $this->values;
    }

    public function addValue(CartValue $value): void
    {
        $this->values->add($value);

        // Recalculate
        $this->recalculateCart();
    }

    public function removeValue(CartValue $value): void
    {
        $this->values->removeElement($value);

        // Recalculate
        $this->recalculateCart();
    }

    /**
     * @return Collection<int, CartRule>|CartRule[]
     */
    public function getCartRules(): Collection
    {
        return $this->cart_rules;
    }

    public function addCartRule(CartRule $cartRule): void
    {
        $this->cart_rules->add($cartRule);

        // Recalculate
        $this->recalculateCart();
    }

    public function removeCartRule(CartRule $cartRule): void
    {
        $this->cart_rules->removeElement($cartRule);

        // Recalculate
        $this->recalculateCart();
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
        $shipmentMethodData = $this->shipment_method_data;

        // Make sure to return actual Money objects! These get lost when storing JSON in the db.
        if (is_array($shipmentMethodData['price'])) {
            ['amount' => $amount, 'currency' => $currency] = $shipmentMethodData['price'];
            $shipmentMethodData['price'] = new Money($amount, new Currency($currency));
        }
        if (is_array($shipmentMethodData['vat']['price'])) {
            ['amount' => $amount, 'currency' => $currency] = $shipmentMethodData['vat']['price'];
            $shipmentMethodData['vat']['price'] = new Money($amount, new Currency($currency));
        }

        return $shipmentMethodData;
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

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function getTotal(): Money
    {
        if (!$this->total) {
            $this->calculateTotals();
        }

        return $this->total;
    }

    public function getSubTotal(): Money
    {
        if (!isset($this->subTotal)) {
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

    public function getVatTotal(): ?Money
    {
        if (!$this->vatTotal) {
            $this->calculateTotals();

            foreach ($this->vats as $vat) {
                $this->vatTotal->add($vat['total']);
            }
        }

        return $this->vatTotal;
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
        $this->subTotal = Money::EUR(0);
        $this->total = Money::EUR(0);
        $this->vats = [];
        $this->totalWeight = 0;

        // Store new values
        /** @var CartValue $value */
        foreach ($this->values as $value) {
            // Update the product totals
            $product = $value->getProduct();
            $vat = $product->getVat();
            $vatPrice = $value->getVatPrice()->multiply($value->getQuantity());

            if ($product->getWeight() !== null) {
                $this->totalWeight += $product->getWeight() * $value->getQuantity();
            }

            $this->subTotal = $this->subTotal->add($value->getTotal());
            $this->total = $this->total->add($value->getTotal())->add($vatPrice);
            $this->addVat($vat, $vatPrice);

            // Update the product option totals
            foreach ($value->getCartValueOptions() as $valueOption) {
                $vat = $valueOption->getVat();

                if (!array_key_exists($vat->getId(), $this->vats)) {
                    $this->vats[$vat->getId()] = [
                        'id' => $vat->getId(),
                        'title' => $vat->getTitle(),
                        'total' => Money::EUR(0),
                    ];
                }

                $vatPrice = $valueOption->getVatPrice()->multiply($value->getQuantity());

                if ($valueOption->isImpactTypeAdd()) {
                    $this->addVat($vat, $vatPrice);
                    $this->total = $this->total->add($vatPrice);
                } elseif ($valueOption->isImpactTypeSubtract()) {
                    $this->addVat($vat, $vatPrice, true);
                    $this->total = $this->total->subtract($vatPrice);
                }
            }
        }

        $this->calculateCartRules();

        // Store the shipment data
        if (isset($this->shipment_method)) {
            $shipmentMethodData = $this->getShipmentMethodData();
            /** @var Money $shipmentPrice */
            $shipmentPrice = $shipmentMethodData['price'];
            /** @var Money $shipmentVatPrice */
            $shipmentVatPrice = $shipmentMethodData['vat']['price'];
            $this->total = $this->total->add($shipmentPrice, $shipmentVatPrice);
            $this->addVat($shipmentMethodData['vat']['id'], $shipmentVatPrice);
        }
    }

    private function recalculateCart(): void
    {
        $this->calculateTotalQuantity();
        $this->calculateTotals();
    }

    private function addVat($vat, ?Money $price, $subtractVat = false): void
    {
        if (is_int($vat)) {
            /** @var VatRepository $vatRepository */
            $vatRepository = Model::get('commerce.repository.vat');
            $vat = $vatRepository->findOneByIdAndLocale($vat, Locale::frontendLanguage());
        }

        if (!array_key_exists($vat->getId(), $this->vats)) {
            $this->vats[$vat->getId()] = [
                'id' => $vat->getId(),
                'title' => $vat->getTitle(),
                'total' => Money::EUR(0),
            ];
        }

        if ($subtractVat) {
            $this->vats[$vat->getId()]['total'] = $this->vats[$vat->getId()]['total']->subtract($price);
        } else {
            $this->vats[$vat->getId()]['total'] = $this->vats[$vat->getId()]['total']->add($price);
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
        if (!isset($this->allProductsInStock)) {
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
            if ($cartRule->getReductionPercentage() !== null) {
                $total = $this->applyPercentageDiscount($cartRule->getReductionPercentage());

                $this->setCartRuleTotal($cartRule, $total);
            }

            if ($cartRule->getReductionPrice() !== null) {
                $total = $this->applyPercentageDiscount($cartRule->getReductionPrice() / $this->total);

                $this->setCartRuleTotal($cartRule, $total);
            }
        }
    }

    private function applyPercentageDiscount(float $percentage): Money
    {
        $discount = $this->subTotal->multiply($percentage);
        $this->subTotal = $this->subTotal->subtract($discount);

        foreach ($this->vats as $key => $vat) {
            $this->vats[$key]['total'] = $this->vats[$key]['total']->subtract($vat['total']->multiply($percentage));
        }

        $this->total = $this->total->subtract($this->total->multiply($percentage));

        return $discount;
    }

    public function getCartRuleTotal(CartRule $cartRule): ?Money
    {
        return $this->cartRuleTotals[$cartRule->getId()] ?? null;
    }

    private function setCartRuleTotal(CartRule $cartRule, Money $total): void
    {
        $this->cartRuleTotals[$cartRule->getId()] = $total;
    }

    /**
     * Convert the cart information to JSON. This helps to make Ajax responses easier.
     */
    public function jsonSerialize(): array
    {
        $this->moneyFormatter = new MoneyFormatter();
        $this->recalculateCart();

        return [
            'totalQuantity' => $this->getTotalQuantity(),
            'cartRules' => array_map(function (CartRule $item) {
                return [
                    'id' => $item->getId(),
                    'title' => $item->getTitle(),
                    'code' => $item->getCode(),
                    'total' => $this->getCartRuleTotal($item) !== null ? $this->moneyFormatter->asFloat($this->getCartRuleTotal($item)) : null,
                ];
            }, $this->getCartRules()->toArray()),
            'subTotal' => $this->moneyFormatter->asFloat($this->getSubTotal()),
            'vats' => array_map(function ($item) {
                return [
                    'id' => $item['id'],
                    'title' => $item['title'],
                    'total' => $this->moneyFormatter->asFloat($item['total']), // Convert Money to plain string
                ];
            }, array_values($this->getVats())),
            'total' => $this->moneyFormatter->asFloat($this->getTotal()),
            'items' => array_map(function (CartValue $cartValue) {
                return [
                    'id' => $cartValue->getId(),
                    'sku' => $cartValue->getProduct()->getSku(),
                    'name' => $cartValue->getProduct()->getTitle(),
                    'url' => $cartValue->getProduct()->getUrl(),
                    'thumbnail' => $cartValue->getProduct()->getThumbnail()->getWebPath('product_thumbnail'),
                    'category' => $cartValue->getProduct()->getCategory()->getFullCategoryPath(),
                    'brand' => $cartValue->getProduct()->getBrand()->getTitle(),
                    'price' => $this->moneyFormatter->asFloat($cartValue->getProduct()->getActivePrice(false)),
                    'quantity' => $cartValue->getQuantity(),
                    'total' => $this->moneyFormatter->asFloat($cartValue->getTotal()),
                ];
            }, $this->getValues()->toArray()),
        ];
    }
}

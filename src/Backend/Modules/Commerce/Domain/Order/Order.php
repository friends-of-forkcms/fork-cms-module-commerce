<?php

namespace Backend\Modules\Commerce\Domain\Order;

use Backend\Modules\Commerce\Domain\Account\Account;
use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Commerce\Domain\OrderHistory\OrderHistory;
use Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct;
use Backend\Modules\Commerce\Domain\OrderRule\OrderRule;
use Backend\Modules\Commerce\Domain\OrderVat\OrderVat;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Money\Money;

/**
 * @ORM\Table(name="commerce_orders")
 * @ORM\Entity(repositoryClass="OrderRepository")
 */
class Order
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Account\Account", inversedBy="orders")
     * @ORM\JoinColumn(name="accountId", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?Account $account;

    /**
     * @ORM\OneToOne(targetEntity="Backend\Modules\Commerce\Domain\Cart\Cart", inversedBy="order")
     * @ORM\JoinColumn(name="cartId", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?Cart $cart;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $invoiceNumber;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $invoiceDate;

    /**
     * @ORM\Column(type="string")
     */
    private string $paymentMethod;

    /**
     * @ORM\Column(type="string")
     */
    private string $shipmentMethod;

    /**
     * @ORM\Embedded(class="\Money\Money", columnPrefix="shipmentPrice")
     */
    private Money $shipmentPrice;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $comment;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $updatedAt;

    /**
     * @var Collection|OrderRule[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\OrderRule\OrderRule", mappedBy="order", cascade={"remove", "persist"})
     */
    private Collection $rules;

    /**
     * @var Collection|OrderProduct[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct", mappedBy="order", cascade={"remove", "persist"})
     */
    private Collection $products;

    /**
     * @var Collection|OrderVat[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\OrderVat\OrderVat", mappedBy="order", cascade={"remove", "persist"})
     * @ORM\OrderBy({"title": "ASC"})
     */
    private Collection $vats;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress")
     * @ORM\JoinColumn(name="invoiceAddressId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private OrderAddress $invoiceAddress;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress")
     * @ORM\JoinColumn(name="shipmentAddressId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private OrderAddress $shipmentAddress;

    /**
     * @var Collection|OrderHistory[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\OrderHistory\OrderHistory", mappedBy="order")
     * @ORM\JoinColumn(name="orderId")
     * @ORM\OrderBy({"createdAt": "DESC"}))
     */
    private Collection $history;

    /**
     * @ORM\Embedded(class="\Money\Money", columnPrefix="subTotal")
     */
    private Money $subTotal;

    /**
     * @ORM\Embedded(class="\Money\Money", columnPrefix="total")
     */
    private Money $total;

    private function __construct(
        Account $account,
        ?Cart $cart,
        DateTimeInterface $createdAt,
        string $paymentMethod,
        string $shipmentMethod,
        Money $shipmentPrice,
        ?string $comment,
        Money $subTotal,
        Money $total,
        OrderAddress $invoiceAddress,
        OrderAddress $shipmentAddress,
        ArrayCollection $rules,
        ArrayCollection $products,
        ArrayCollection $vats,
        ?string $invoiceNumber,
        ?DateTimeInterface $invoiceDate
    ) {
        $this->account = $account;
        $this->cart = $cart;
        $this->createdAt = $createdAt;
        $this->paymentMethod = $paymentMethod;
        $this->shipmentMethod = $shipmentMethod;
        $this->shipmentPrice = $shipmentPrice;
        $this->comment = $comment;
        $this->subTotal = $subTotal;
        $this->total = $total;
        $this->invoiceAddress = $invoiceAddress;
        $this->shipmentAddress = $shipmentAddress;
        $this->rules = $rules;
        $this->products = $products;
        $this->vats = $vats;
        $this->invoiceNumber = $invoiceNumber;
        $this->invoiceDate = $invoiceDate;
    }

    public static function fromDataTransferObject(OrderDataTransferObject $dataTransferObject): Order
    {
        if ($dataTransferObject->hasExistingOrder()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(OrderDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->account,
            $dataTransferObject->cart,
            $dataTransferObject->createdAt,
            $dataTransferObject->paymentMethod,
            $dataTransferObject->shipment_method,
            $dataTransferObject->shipment_price,
            $dataTransferObject->comment,
            $dataTransferObject->sub_total,
            $dataTransferObject->total,
            $dataTransferObject->invoiceAddress,
            $dataTransferObject->shipmentAddress,
            $dataTransferObject->rules,
            $dataTransferObject->products,
            $dataTransferObject->vats,
            $dataTransferObject->invoiceNumber,
            $dataTransferObject->invoiceDate
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function getInvoiceDate(): ?DateTimeInterface
    {
        return $this->invoiceDate;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function getShipmentMethod(): string
    {
        return $this->shipmentMethod;
    }

    public function getShipmentPrice(): ?Money
    {
        return $this->shipmentPrice;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getSubTotal(): Money
    {
        return $this->subTotal;
    }

    public function getTotal(): Money
    {
        return $this->total;
    }

    /**
     * @return Collection|OrderRule[]
     */
    public function getRules(): Collection
    {
        return $this->rules;
    }

    /**
     * @return Collection|OrderProduct[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    /**
     * @return Collection|OrderVat[]
     */
    public function getVats(): Collection
    {
        return $this->vats;
    }

    public function getInvoiceAddress(): OrderAddress
    {
        return $this->invoiceAddress;
    }

    public function getShipmentAddress(): OrderAddress
    {
        return $this->shipmentAddress;
    }

    /**
     * @return Collection|OrderHistory[]
     */
    public function getHistory(): Collection
    {
        return $this->history;
    }

    private static function update(OrderDataTransferObject $dataTransferObject): Order
    {
        $order = $dataTransferObject->getOrderEntity();

        $order->account = $dataTransferObject->account;
        $order->cart = $dataTransferObject->cart;
        $order->createdAt = $dataTransferObject->createdAt;
        $order->total = $dataTransferObject->total;
        $order->invoiceNumber = $dataTransferObject->invoiceNumber;
        $order->invoiceDate = $dataTransferObject->invoiceDate;
        $order->shipmentMethod = $dataTransferObject->shipment_method;
        $order->shipmentPrice = $dataTransferObject->shipment_price;
        $order->shipmentAddress = $dataTransferObject->shipmentAddress;
        $order->paymentMethod = $dataTransferObject->paymentMethod;
        $order->rules = $dataTransferObject->rules;

        return $order;
    }

    /**
     * @return OrderHistory
     */
    public function getLastHistory(): ?OrderHistory
    {
        return $this->getHistory()->last();
    }

    public function getDataTransferObject(): OrderDataTransferObject
    {
        return new OrderDataTransferObject($this);
    }
}

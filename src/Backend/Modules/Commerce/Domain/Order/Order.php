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

/**
 * @ORM\Table(name="commerce_orders")
 * @ORM\Entity(repositoryClass="OrderRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Order
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Account\Account", inversedBy="orders")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?Account $account;

    /**
     * @ORM\OneToOne(targetEntity="Backend\Modules\Commerce\Domain\Cart\Cart", inversedBy="order")
     * @ORM\JoinColumn(name="cart_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?Cart $cart;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $invoice_number;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $invoice_date;

    /**
     * @ORM\Column(type="string")
     */
    private string $payment_method;

    /**
     * @ORM\Column(type="string")
     */
    private string $shipment_method;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private float $shipment_price;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $comment;

    /**
     * @ORM\Column(type="datetime", name="date")
     */
    private DateTimeInterface $date;

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
     * @ORM\JoinColumn(name="invoice_address_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private OrderAddress $invoice_address;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress")
     * @ORM\JoinColumn(name="shipment_address_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private OrderAddress $shipment_address;

    /**
     * @var Collection|OrderHistory[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\OrderHistory\OrderHistory", mappedBy="order")
     * @ORM\JoinColumn(name="order_id")
     * @ORM\OrderBy({"created_at": "DESC"}))
     */
    private Collection $history;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private float $sub_total;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private float $total;

    private function __construct(
        Account $account,
        ?Cart $cart,
        DateTime $date,
        string $paymentMethod,
        string $shipment_method,
        string $shipment_price,
        ?string $comment,
        float $sub_total,
        float $total,
        OrderAddress $invoiceAddress,
        OrderAddress $shipmentAddress,
        ArrayCollection $rules,
        ArrayCollection $products,
        ArrayCollection $vats,
        ?string $invoiceNumber,
        ?DateTime $invoiceDate
    ) {
        $this->account = $account;
        $this->cart = $cart;
        $this->date = $date;
        $this->payment_method = $paymentMethod;
        $this->shipment_method = $shipment_method;
        $this->shipment_price = $shipment_price;
        $this->comment = $comment;
        $this->sub_total = $sub_total;
        $this->total = $total;
        $this->invoice_address = $invoiceAddress;
        $this->shipment_address = $shipmentAddress;
        $this->rules = $rules;
        $this->products = $products;
        $this->vats = $vats;
        $this->invoice_number = $invoiceNumber;
        $this->invoice_date = $invoiceDate;
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
            $dataTransferObject->date,
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
        return $this->invoice_number;
    }

    public function getInvoiceDate(): ?DateTimeInterface
    {
        return $this->invoice_date;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->payment_method;
    }

    public function getShipmentMethod(): string
    {
        return $this->shipment_method;
    }

    public function getShipmentPrice(): ?string
    {
        return $this->shipment_price;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function getSubTotal(): float
    {
        return $this->sub_total;
    }

    public function getTotal(): float
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
        return $this->invoice_address;
    }

    public function getShipmentAddress(): OrderAddress
    {
        return $this->shipment_address;
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
        $order->date = $dataTransferObject->date;
        $order->total = $dataTransferObject->total;
        $order->invoice_number = $dataTransferObject->invoiceNumber;
        $order->invoice_date = $dataTransferObject->invoiceDate;
        $order->shipment_method = $dataTransferObject->shipment_method;
        $order->shipment_price = $dataTransferObject->shipment_price;
        $order->shipment_address = $dataTransferObject->shipmentAddress;
        $order->payment_method = $dataTransferObject->paymentMethod;
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

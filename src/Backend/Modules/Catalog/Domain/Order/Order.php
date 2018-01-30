<?php

namespace Backend\Modules\Catalog\Domain\Order;

use Backend\Modules\Catalog\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Catalog\Domain\OrderProduct\OrderProduct;
use Backend\Modules\Catalog\Domain\OrderVat\OrderVat;
use Backend\Modules\Catalog\Domain\Product\Product;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_orders")
 * @ORM\Entity(repositoryClass="OrderRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Order
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
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $shipment_method;

    /**
     * @var integer
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $shipment_price;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", name="date")
     */
    private $date;

    /**
     * @var OrderProduct[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\OrderProduct\OrderProduct", mappedBy="order")
     * @ORM\OrderBy({"sequence" = "ASC"})
     */
    private $products;

    /**
     * @var OrderVat[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\OrderVat\OrderVat", mappedBy="order")
     * @ORM\OrderBy({"title" = "ASC"})
     */
    private $vats;

    /**
     * @var OrderAddress
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\OrderAddress\OrderAddress", inversedBy="order")
     * @ORM\JoinColumn(name="invoice_address_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $invoice_address;

    /**
     * @var OrderAddress
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\OrderAddress\OrderAddress", inversedBy="order")
     * @ORM\JoinColumn(name="shipment_address_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $shipment_address;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $sub_total;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $total;

    private function __construct(
        DateTime $date,
        string $shipment_method,
        string $shipment_price,
        ?string $comment,
        float $sub_total,
        float $total,
        OrderAddress $invoiceAddress,
        OrderAddress $shipmentAddress,
        ArrayCollection $products,
        ArrayCollection $vats
    ) {
        $this->date             = $date;
        $this->shipment_method  = $shipment_method;
        $this->shipment_price   = $shipment_price;
        $this->comment          = $comment;
        $this->sub_total        = $sub_total;
        $this->total            = $total;
        $this->invoice_address  = $invoiceAddress;
        $this->shipment_address = $shipmentAddress;
        $this->products         = $products;
        $this->vats             = $vats;
    }

    public static function fromDataTransferObject(OrderDataTransferObject $dataTransferObject)
    {
        if ($dataTransferObject->hasExistingOrder()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(OrderDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->date,
            $dataTransferObject->shipment_method,
            $dataTransferObject->shipment_price,
            $dataTransferObject->comment,
            $dataTransferObject->sub_total,
            $dataTransferObject->total,
            $dataTransferObject->invoiceAddress,
            $dataTransferObject->shipmentAddress,
            $dataTransferObject->products,
            $dataTransferObject->vats
        );
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getShipmentMethod(): string
    {
        return $this->shipment_method;
    }

    /**
     * @return int
     */
    public function getShipmentPrice(): int
    {
        return $this->shipment_price;
    }

    /**
     * @return string
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @return float
     */
    public function getSubTotal(): float
    {
        return $this->sub_total;
    }

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * @return OrderProduct[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @return OrderVat[]
     */
    public function getVats()
    {
        return $this->vats;
    }

    /**
     * @return OrderAddress
     */
    public function getInvoiceAddress(): OrderAddress
    {
        return $this->invoice_address;
    }

    /**
     * @return OrderAddress
     */
    public function getShipmentAddress(): OrderAddress
    {
        return $this->shipment_address;
    }

    private static function update(OrderDataTransferObject $dataTransferObject)
    {
        $order = $dataTransferObject->getOrderEntity();

        $order->date  = $dataTransferObject->date;
        $order->total = $dataTransferObject->total;

        return $order;
    }

    public function getDataTransferObject(): OrderDataTransferObject
    {
        return new OrderDataTransferObject($this);
    }
}

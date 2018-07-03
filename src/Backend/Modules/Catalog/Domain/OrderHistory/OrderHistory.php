<?php

namespace Backend\Modules\Catalog\Domain\OrderHistory;

use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatus;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_order_histories")
 * @ORM\Entity(repositoryClass="OrderHistoryRepository")
 * @ORM\HasLifecycleCallbacks
 */
class OrderHistory
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
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\Order\Order", inversedBy="order_histories")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $order;

    /**
     * @var OrderStatus
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\OrderStatus\OrderStatus", inversedBy="order_histories")
     * @ORM\JoinColumn(name="order_status_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $order_status;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $message;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $notify;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $attach_invoice;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    private function __construct(
        Order $order,
        OrderStatus $order_status,
        ?string $message,
        bool $notify,
        bool $attach_invoice,
        \DateTime $created_at
    )
    {
        $this->order = $order;
        $this->order_status = $order_status;
        $this->message = $message;
        $this->notify = $notify;
        $this->attach_invoice = $attach_invoice;
        $this->created_at = $created_at;
    }

    public static function fromDataTransferObject(OrderHistoryDataTransferObject $dataTransferObject)
    {
        return self::create($dataTransferObject);
    }

    private static function create(OrderHistoryDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->order,
            $dataTransferObject->orderStatus,
            $dataTransferObject->message,
            $dataTransferObject->notify,
            $dataTransferObject->attach_invoice,
            $dataTransferObject->created_at
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @return OrderStatus
     */
    public function getOrderStatus(): OrderStatus
    {
        return $this->order_status;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    public function isNotify(): bool
    {
        return $this->notify;
    }

    /**
     * @return bool
     */
    public function isAttachInvoice(): bool
    {
        return $this->attach_invoice;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }

    public function getDataTransferObject(): OrderHistoryDataTransferObject
    {
        return new OrderHistoryDataTransferObject($this);
    }
}

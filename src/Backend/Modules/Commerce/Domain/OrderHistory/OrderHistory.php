<?php

namespace Backend\Modules\Commerce\Domain\OrderHistory;

use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="commerce_order_histories")
 * @ORM\Entity(repositoryClass="OrderHistoryRepository")
 * @ORM\HasLifecycleCallbacks
 */
class OrderHistory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Order\Order", inversedBy="history")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private Order $order;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus", inversedBy="order_histories")
     * @ORM\JoinColumn(name="order_status_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private OrderStatus $order_status;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $created_at;

    private function __construct(
        Order $order,
        OrderStatus $order_status,
        DateTimeInterface $created_at
    ) {
        $this->order = $order;
        $this->order_status = $order_status;
        $this->created_at = $created_at;
    }

    public static function fromDataTransferObject(OrderHistoryDataTransferObject $dataTransferObject): OrderHistory
    {
        return self::create($dataTransferObject);
    }

    private static function create(OrderHistoryDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->order,
            $dataTransferObject->orderStatus,
            $dataTransferObject->created_at
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getOrderStatus(): OrderStatus
    {
        return $this->order_status;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->created_at;
    }

    public function getDataTransferObject(): OrderHistoryDataTransferObject
    {
        return new OrderHistoryDataTransferObject($this);
    }
}

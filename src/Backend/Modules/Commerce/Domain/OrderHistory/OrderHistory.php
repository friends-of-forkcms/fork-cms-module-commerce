<?php

namespace Backend\Modules\Commerce\Domain\OrderHistory;

use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="commerce_order_histories")
 * @ORM\Entity(repositoryClass="OrderHistoryRepository")
 */
class OrderHistory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Order\Order", inversedBy="history")
     * @ORM\JoinColumn(name="orderId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private Order $order;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus", inversedBy="orderHistories")
     * @ORM\JoinColumn(name="orderStatusId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private OrderStatus $orderStatus;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdAt;

    private function __construct(
        Order $order,
        OrderStatus $orderStatus,
        DateTimeInterface $createdAt
    ) {
        $this->order = $order;
        $this->orderStatus = $orderStatus;
        $this->createdAt = $createdAt;
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
            $dataTransferObject->createdAt
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
        return $this->orderStatus;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getDataTransferObject(): OrderHistoryDataTransferObject
    {
        return new OrderHistoryDataTransferObject($this);
    }
}

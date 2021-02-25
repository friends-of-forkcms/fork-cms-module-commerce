<?php

namespace Backend\Modules\Commerce\Domain\OrderHistory;

use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="commerce_order_histories")
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
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Order\Order", inversedBy="history")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $order;

    /**
     * @var OrderStatus
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus", inversedBy="order_histories")
     * @ORM\JoinColumn(name="order_status_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $order_status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    private function __construct(
        Order $order,
        OrderStatus $order_status,
        \DateTime $created_at
    )
    {
        $this->order = $order;
        $this->order_status = $order_status;
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

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
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="created_on", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdOn;

    private function __construct(
        Order $order,
        OrderStatus $order_status,
        DateTimeInterface $createdOn
    ) {
        $this->order = $order;
        $this->order_status = $order_status;
        $this->createdOn = $createdOn;
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
            $dataTransferObject->createdOn
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

    public function getCreatedOn(): DateTimeInterface
    {
        return $this->createdOn;
    }

    public function getDataTransferObject(): OrderHistoryDataTransferObject
    {
        return new OrderHistoryDataTransferObject($this);
    }
}

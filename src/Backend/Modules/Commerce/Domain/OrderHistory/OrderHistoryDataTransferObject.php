<?php

namespace Backend\Modules\Commerce\Domain\OrderHistory;

use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus;
use DateTime;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class OrderHistoryDataTransferObject
{
    protected ?OrderHistory $orderHistoryEntity = null;
    public int $id;
    public Order $order;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public OrderStatus $orderStatus;
    public DateTimeInterface $createdAt;

    public function __construct(OrderHistory $orderHistory = null)
    {
        $this->orderHistoryEntity = $orderHistory;
        $this->createdAt = new DateTime();

        if (!$this->hasExistingOrderHistory()) {
            return;
        }

        $this->id = $this->orderHistoryEntity->getId();
        $this->order = $this->orderHistoryEntity->getOrder();
        $this->orderStatus = $this->orderHistoryEntity->getOrderStatus();
        $this->createdAt = $this->orderHistoryEntity->getCreatedAt();
    }

    public function getOrderHistoryEntity(): OrderHistory
    {
        return $this->orderHistoryEntity;
    }

    public function hasExistingOrderHistory(): bool
    {
        return $this->orderHistoryEntity instanceof OrderHistory;
    }
}

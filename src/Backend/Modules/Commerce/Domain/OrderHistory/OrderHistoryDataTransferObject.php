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
    public DateTimeInterface $created_at;

    public function __construct(OrderHistory $orderHistory = null)
    {
        $this->orderHistoryEntity = $orderHistory;
        $this->created_at = new DateTime();

        if (!$this->hasExistingOrderHistory()) {
            return;
        }

        $this->id = $this->orderHistoryEntity->getId();
        $this->order = $this->orderHistoryEntity->getOrder();
        $this->orderStatus = $this->orderHistoryEntity->getOrderStatus();
        $this->created_at = $this->orderHistoryEntity->getCreatedAt();
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

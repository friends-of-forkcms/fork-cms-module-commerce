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
    public DateTimeInterface $createdOn;

    public function __construct(OrderHistory $orderHistory = null)
    {
        $this->orderHistoryEntity = $orderHistory;
        $this->createdOn = new DateTime();

        if (!$this->hasExistingOrderHistory()) {
            return;
        }

        $this->id = $this->orderHistoryEntity->getId();
        $this->order = $this->orderHistoryEntity->getOrder();
        $this->orderStatus = $this->orderHistoryEntity->getOrderStatus();
        $this->createdOn = $this->orderHistoryEntity->getCreatedOn();
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

<?php

namespace Backend\Modules\Catalog\Domain\OrderHistory;

use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatus;
use Symfony\Component\Validator\Constraints as Assert;

class OrderHistoryDataTransferObject
{
    /**
     * @var OrderHistory
     */
    protected $orderHistoryEntity;

    /**
     * @var int
     */
    public $id;

    /**
     * @var Order
     */
    public $order;

    /**
     * @var OrderStatus
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $orderStatus;

    /**
     * @var \DateTime
     */
    public $created_at;

    public function __construct(OrderHistory $orderHistory = null)
    {
        $this->orderHistoryEntity = $orderHistory;
        $this->created_at = new \DateTime();

        if (!$this->hasExistingOrderHistory()) {
            return;
        }

        $this->id = $orderHistory->getId();
        $this->order = $orderHistory->getOrder();
        $this->orderStatus = $orderHistory->getOrderStatus();
        $this->created_at = $orderHistory->getCreatedAt();
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

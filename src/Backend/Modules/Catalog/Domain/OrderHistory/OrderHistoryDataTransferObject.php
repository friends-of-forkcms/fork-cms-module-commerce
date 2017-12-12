<?php

namespace Backend\Modules\Catalog\Domain\OrderHistory;

use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\OrderHistoryValue\OrderHistoryValue;
use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatus;
use Common\Doctrine\Entity\Meta;
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
     */
    public $orderStatus;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $message;

    /**
     * @var bool
     */
    public $notify;

    /**
     * @var \DateTime
     */
    public $created_at;

    public function __construct(OrderHistory $orderHistory = null)
    {
        $this->orderHistoryEntity = $orderHistory;
        $this->notify             = false;
        $this->created_at         = new \DateTime();

        if ( ! $this->hasExistingOrderHistory()) {
            return;
        }

        $this->id          = $orderHistory->getId();
        $this->order       = $orderHistory->getOrder();
        $this->orderStatus = $orderHistory->getOrderStatus();
        $this->message     = $orderHistory->getMessage();
        $this->notify      = $orderHistory->isNotify();
        $this->created_at  = $orderHistory->getCreatedAt();
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

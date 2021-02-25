<?php

namespace Backend\Modules\Commerce\Domain\OrderProductNotification;

use Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct;
use Symfony\Component\Validator\Constraints as Assert;

class OrderProductNotificationDataTransferObject
{
    /**
     * @var OrderProductNotification
     */
    protected $orderProductNotificationEntity;

    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $type;

    /**
     * @var OrderProduct
     */
    public $order_product;

    /**
     * @var string
     */
    public $message;

    public function __construct(OrderProductNotification $orderProductNotification = null)
    {
        $this->orderProductNotificationEntity = $orderProductNotification;

        if (!$this->hasExistingOrderProductNotification()) {
            return;
        }

        $this->id = $orderProductNotification->getId();
        $this->order_product = $orderProductNotification->getOrderProduct();
        $this->message = $orderProductNotification->getMessage();
    }

    public function getOrderProductNotificationEntity(): OrderProductNotification
    {
        return $this->orderProductNotificationEntity;
    }

    public function hasExistingOrderProductNotification(): bool
    {
        return $this->orderProductNotificationEntity instanceof OrderProductNotification;
    }
}

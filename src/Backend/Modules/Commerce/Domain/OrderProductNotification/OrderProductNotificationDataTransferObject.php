<?php

namespace Backend\Modules\Commerce\Domain\OrderProductNotification;

use Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct;

class OrderProductNotificationDataTransferObject
{
    protected ?OrderProductNotification $orderProductNotificationEntity = null;
    public int $id;
    public int $type;
    public OrderProduct $order_product;
    public string $message;

    public function __construct(OrderProductNotification $orderProductNotification = null)
    {
        $this->orderProductNotificationEntity = $orderProductNotification;

        if (!$this->hasExistingOrderProductNotification()) {
            return;
        }

        $this->id = $this->orderProductNotificationEntity->getId();
        $this->order_product = $this->orderProductNotificationEntity->getOrderProduct();
        $this->message = $this->orderProductNotificationEntity->getMessage();
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

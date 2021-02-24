<?php

namespace Backend\Modules\Commerce\Domain\OrderVat;

use Backend\Modules\Commerce\Domain\Order\Order;

class OrderVatDataTransferObject
{
    protected ?OrderVat $orderVatEntity = null;
    public int $id;
    public Order $order;
    public string $title;
    public float $total;

    public function __construct(OrderVat $orderVat = null)
    {
        $this->orderVatEntity = $orderVat;

        if (!$this->hasExistingOrderVat()) {
            return;
        }

        $this->id = $this->orderVatEntity->getId();
        $this->order = $this->orderVatEntity->getOrder();
        $this->title = $this->orderVatEntity->getTitle();
        $this->total = $this->orderVatEntity->getTotal();
    }

    public function getOrderVatEntity(): OrderVat
    {
        return $this->orderVatEntity;
    }

    public function hasExistingOrderVat(): bool
    {
        return $this->orderVatEntity instanceof OrderVat;
    }
}

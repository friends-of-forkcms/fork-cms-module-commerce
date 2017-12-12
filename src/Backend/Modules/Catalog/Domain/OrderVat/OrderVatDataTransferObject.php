<?php

namespace Backend\Modules\Catalog\Domain\OrderVat;

use Backend\Modules\Catalog\Domain\Order\Order;
use Symfony\Component\Validator\Constraints as Assert;

class OrderVatDataTransferObject
{
    /**
     * @var OrderVat
     */
    protected $orderVatEntity;

    /**
     * @var int
     */
    public $id;

    /**
     * @var Order
     */
    public $order;

    /**
     * @var string
     */
    public $title;

    /**
     * @var float
     */
    public $total;

    public function __construct(OrderVat $orderVat = null)
    {
        $this->orderVatEntity = $orderVat;

        if ( ! $this->hasExistingOrderVat()) {
            return;
        }

        $this->id    = $orderVat->getId();
        $this->order = $orderVat->getOrder();
        $this->title = $orderVat->getTitle();
        $this->total = $orderVat->getTotal();
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

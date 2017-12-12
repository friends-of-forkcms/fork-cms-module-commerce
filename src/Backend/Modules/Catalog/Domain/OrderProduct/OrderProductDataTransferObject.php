<?php

namespace Backend\Modules\Catalog\Domain\OrderProduct;

use Backend\Modules\Catalog\Domain\Order\Order;
use Symfony\Component\Validator\Constraints as Assert;

class OrderProductDataTransferObject
{
    /**
     * @var OrderProduct
     */
    protected $orderProductEntity;

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
    public $sku;

    /**
     * @var string
     */
    public $title;

    /**
     * @var int
     */
    public $amount;

    /**
     * @var float
     */
    public $price;

    /**
     * @var float
     */
    public $total;

    public function __construct(OrderProduct $orderProduct = null)
    {
        $this->orderProductEntity = $orderProduct;

        if ( ! $this->hasExistingOrderProduct()) {
            return;
        }

        $this->id     = $orderProduct->getId();
        $this->order  = $orderProduct->getOrder();
        $this->sku    = $orderProduct->getSku();
        $this->title  = $orderProduct->getTitle();
        $this->amount = $orderProduct->getAmount();
        $this->price  = $orderProduct->getPrice();
        $this->total  = $orderProduct->getTotal();
    }

    public function getOrderProductEntity(): OrderProduct
    {
        return $this->orderProductEntity;
    }

    public function hasExistingOrderProduct(): bool
    {
        return $this->orderProductEntity instanceof OrderProduct;
    }
}

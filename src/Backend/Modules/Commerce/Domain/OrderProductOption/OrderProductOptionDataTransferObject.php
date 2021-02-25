<?php

namespace Backend\Modules\Commerce\Domain\OrderProductOption;

use Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct;
use Symfony\Component\Validator\Constraints as Assert;

class OrderProductOptionDataTransferObject
{
    /**
     * @var OrderProductOption
     */
    protected $orderProductOptionEntity;

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
    public $sku;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $value;

    /**
     * @var float
     */
    public $price;

    /**
     * @var float
     */
    public $total;

    public function __construct(OrderProductOption $orderProductOption = null)
    {
        $this->orderProductOptionEntity = $orderProductOption;

        if (!$this->hasExistingOrderProductOption()) {
            return;
        }

        $this->id = $orderProductOption->getId();
        $this->order_product = $orderProductOption->getOrderProduct();
        $this->sku = $orderProductOption->getSku();
        $this->title = $orderProductOption->getTitle();
        $this->value = $orderProductOption->getValue();
        $this->price = $orderProductOption->getPrice();
        $this->total = $orderProductOption->getTotal();
    }

    public function getOrderProductOptionEntity(): OrderProductOption
    {
        return $this->orderProductOptionEntity;
    }

    public function hasExistingOrderProductOption(): bool
    {
        return $this->orderProductOptionEntity instanceof OrderProductOption;
    }
}

<?php

namespace Backend\Modules\Commerce\Domain\OrderProductOption;

use Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct;
use Money\Money;

class OrderProductOptionDataTransferObject
{
    protected ?OrderProductOption $orderProductOptionEntity = null;
    public int $id;
    public int $type;
    public OrderProduct $order_product;
    public ?string $sku = null;
    public string $title;
    public string $value;
    public Money $price;
    public Money $total;

    public function __construct(OrderProductOption $orderProductOption = null)
    {
        $this->orderProductOptionEntity = $orderProductOption;

        if (!$this->hasExistingOrderProductOption()) {
            return;
        }

        $this->id = $this->orderProductOptionEntity->getId();
        $this->order_product = $this->orderProductOptionEntity->getOrderProduct();
        $this->sku = $this->orderProductOptionEntity->getSku();
        $this->title = $this->orderProductOptionEntity->getTitle();
        $this->value = $this->orderProductOptionEntity->getValue();
        $this->price = $this->orderProductOptionEntity->getPrice();
        $this->total = $this->orderProductOptionEntity->getTotal();
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

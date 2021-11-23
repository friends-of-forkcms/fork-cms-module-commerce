<?php

namespace Backend\Modules\Commerce\Domain\OrderProduct;

use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\OrderProductNotification\OrderProductNotification;
use Backend\Modules\Commerce\Domain\OrderProductOption\OrderProductOption;
use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Money\Money;

class OrderProductDataTransferObject
{
    protected ?OrderProduct $orderProductEntity = null;
    public int $id;
    public ?Product $product = null;
    public int $type;
    public Order $order;
    public string $sku;
    public string $title;
    public int $amount;
    public ?int $width = null;
    public ?int $height = null;
    public ?int $order_width = null;
    public ?int $order_height = null;
    public Money $price;
    public Money $total;

    /**
     * @var Collection|ProductOption[]
     */
    public Collection $productOptions;

    /**
     * @var Collection|OrderProductNotification[]
     */
    public Collection $productNotifications;

    public function __construct(OrderProduct $orderProduct = null)
    {
        $this->orderProductEntity = $orderProduct;
        $this->productOptions = new ArrayCollection();
        $this->productNotifications = new ArrayCollection();

        if (!$this->hasExistingOrderProduct()) {
            return;
        }

        $this->id = $this->orderProductEntity->getId();
        $this->product = $this->orderProductEntity->getProduct();
        $this->type = $this->orderProductEntity->getType();
        $this->order = $this->orderProductEntity->getOrder();
        $this->sku = $this->orderProductEntity->getSku();
        $this->title = $this->orderProductEntity->getTitle();
        $this->amount = $this->orderProductEntity->getAmount();
        $this->width = $this->orderProductEntity->getWidth();
        $this->height = $this->orderProductEntity->getHeight();
        $this->order_width = $this->orderProductEntity->getOrderWidth();
        $this->order_height = $this->orderProductEntity->getOrderHeight();
        $this->price = $this->orderProductEntity->getPrice();
        $this->total = $this->orderProductEntity->getTotal();
        $this->productOptions = $this->orderProductEntity->getProductOptions();
        $this->productNotifications = $this->orderProductEntity->getProductNotifications();
    }

    public function addProductOption(OrderProductOption $productOption): void
    {
        $this->productOptions->add($productOption);
    }

    public function addProductNotification(OrderProductNotification $productNotification): void
    {
        $this->productNotifications->add($productNotification);
    }

    public function setOrderProductEntity(OrderProduct $orderProductEntity): void
    {
        $this->orderProductEntity = $orderProductEntity;
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

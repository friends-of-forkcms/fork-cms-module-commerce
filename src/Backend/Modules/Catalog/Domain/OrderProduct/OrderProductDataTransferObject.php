<?php

namespace Backend\Modules\Catalog\Domain\OrderProduct;

use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\OrderProductNotification\OrderProductNotification;
use Backend\Modules\Catalog\Domain\OrderProductOption\OrderProductOption;
use Backend\Modules\Catalog\Domain\Product\Product;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @var Product
     */
    public $product;

    /**
     * @var int
     */
    public $type;

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
     * @var int
     */
    public $width;

    /**
     * @var int
     */
    public $height;

    /**
     * @var int
     */
    public $order_width;

    /**
     * @var int
     */
    public $order_height;

    /**
     * @var float
     */
    public $price;

    /**
     * @var float
     */
    public $total;

    /**
     * @var ArrayCollection
     */
    public $productOptions;

    /**
     * @var ArrayCollection
     */
    public $productNotifications;

    public function __construct(OrderProduct $orderProduct = null)
    {
        $this->orderProductEntity = $orderProduct;
        $this->productOptions = new ArrayCollection();
        $this->productNotifications = new ArrayCollection();

        if (!$this->hasExistingOrderProduct()) {
            return;
        }

        $this->id = $orderProduct->getId();
        $this->product = $orderProduct->getProduct();
        $this->type = $orderProduct->getType();
        $this->order = $orderProduct->getOrder();
        $this->sku = $orderProduct->getSku();
        $this->title = $orderProduct->getTitle();
        $this->amount = $orderProduct->getAmount();
        $this->width = $orderProduct->getWidth();
        $this->height = $orderProduct->getHeight();
        $this->order_width = $orderProduct->getOrderWidth();
        $this->order_height = $orderProduct->getOrderHeight();
        $this->price = $orderProduct->getPrice();
        $this->total = $orderProduct->getTotal();
        $this->productOptions = $orderProduct->getProductOptions();
        $this->productNotifications = $orderProduct->getProductNotifications();
    }

    /**
     * @param OrderProductOption $productOption
     */
    public function addProductOption(OrderProductOption $productOption): void
    {
        $this->productOptions->add($productOption);
    }

    /**
     * @param OrderProductNotification $productNotification
     */
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

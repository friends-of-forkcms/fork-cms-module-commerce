<?php

namespace Backend\Modules\Commerce\Domain\Order;

use Backend\Modules\Commerce\Domain\Account\Account;
use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct;
use Backend\Modules\Commerce\Domain\OrderRule\OrderRule;
use Backend\Modules\Commerce\Domain\OrderVat\OrderVat;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class OrderDataTransferObject
{
    protected ?Order $orderEntity = null;
    public int $id;
    public Account $account;
    public ?Cart $cart = null;
    public ?string $paymentMethod = null;
    public string $shipment_method;
    public ?string $shipment_price = null;
    public DateTimeInterface $date;
    public ?string $comment = null;
    public float $sub_total;
    public float $total;
    public ?string $invoiceNumber = null;
    public ?DateTimeInterface $invoiceDate = null;
    public OrderAddress $invoiceAddress;
    public OrderAddress $shipmentAddress;

    /**
     * @var Collection|OrderRule[]
     */
    public Collection $rules;

    /**
     * @var Collection|Product[]
     */
    public Collection $products;

    /**
     * @var Collection|Vat[]
     */
    public $vats;

    public function __construct(Order $order = null)
    {
        $this->orderEntity = $order;
        $this->date = new DateTime();
        $this->rules = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->vats = new ArrayCollection();

        if (!$this->hasExistingOrder()) {
            return;
        }

        $this->id = $this->orderEntity->getId();
        $this->account = $this->orderEntity->getAccount();
        $this->cart = $this->orderEntity->getCart();
        $this->paymentMethod = $this->orderEntity->getPaymentMethod();
        $this->shipment_method = $this->orderEntity->getShipmentMethod();
        $this->shipment_price = $this->orderEntity->getShipmentPrice();
        $this->date = $this->orderEntity->getDate();
        $this->comment = $this->orderEntity->getComment();
        $this->sub_total = $this->orderEntity->getSubTotal();
        $this->total = $this->orderEntity->getTotal();
        $this->invoiceNumber = $this->orderEntity->getInvoiceNumber();
        $this->invoiceDate = $this->orderEntity->getInvoiceDate();
        $this->invoiceAddress = $this->orderEntity->getInvoiceAddress();
        $this->shipmentAddress = $this->orderEntity->getShipmentAddress();
        $this->rules = $this->orderEntity->getRules();
        $this->products = $this->orderEntity->getProducts();
        $this->vats = $this->orderEntity->getVats();
    }

    public function setOrderEntity(Order $orderEntity): void
    {
        $this->orderEntity = $orderEntity;
    }

    public function addRule(OrderRule $orderRule): void
    {
        $this->rules->add($orderRule);
    }

    public function removeRule(OrderRule $orderRule): void
    {
        $this->rules->removeElement($orderRule);
    }

    public function addProduct(OrderProduct $product): void
    {
        $this->products->add($product);
    }

    public function addVat(OrderVat $vat): void
    {
        $this->vats->add($vat);
    }

    public function getOrderEntity(): Order
    {
        return $this->orderEntity;
    }

    public function hasExistingOrder(): bool
    {
        return $this->orderEntity instanceof Order;
    }
}

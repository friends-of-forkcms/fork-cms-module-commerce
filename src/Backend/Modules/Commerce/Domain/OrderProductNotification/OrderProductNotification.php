<?php

namespace Backend\Modules\Commerce\Domain\OrderProductNotification;

use Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="commerce_order_product_notifications")
 * @ORM\Entity(repositoryClass="OrderProductNotificationRepository")
 * @ORM\HasLifecycleCallbacks
 */
class OrderProductNotification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct", inversedBy="product_notifications")
     * @ORM\JoinColumn(name="order_product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private OrderProduct $order_product;

    /**
     * @ORM\Column(type="string")
     */
    private string $message;

    public function getDataTransferObject(): OrderProductNotificationDataTransferObject
    {
        return new OrderProductNotificationDataTransferObject($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrderProduct(): OrderProduct
    {
        return $this->order_product;
    }

    public function setOrderProduct(OrderProduct $order_product): void
    {
        $this->order_product = $order_product;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}

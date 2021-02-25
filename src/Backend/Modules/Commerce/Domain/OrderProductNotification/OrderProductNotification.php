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
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @var OrderProduct
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct", inversedBy="product_notifications")
     * @ORM\JoinColumn(name="order_product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $order_product;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $message;

    public function getDataTransferObject(): OrderProductNotificationDataTransferObject
    {
        return new OrderProductNotificationDataTransferObject($this);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return OrderProduct
     */
    public function getOrderProduct(): OrderProduct
    {
        return $this->order_product;
    }

    /**
     * @param OrderProduct $order_product
     */
    public function setOrderProduct(OrderProduct $order_product): void
    {
        $this->order_product = $order_product;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}

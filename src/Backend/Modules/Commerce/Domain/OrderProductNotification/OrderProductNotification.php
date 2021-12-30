<?php

namespace Backend\Modules\Commerce\Domain\OrderProductNotification;

use Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="commerce_order_product_notifications")
 * @ORM\Entity(repositoryClass="OrderProductNotificationRepository")
 */
class OrderProductNotification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct", inversedBy="productNotifications")
     * @ORM\JoinColumn(name="orderProductId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private OrderProduct $orderProduct;

    /**
     * @ORM\Column(type="string")
     */
    private string $message;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdAt;

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
        return $this->orderProduct;
    }

    public function setOrderProduct(OrderProduct $orderProduct): void
    {
        $this->orderProduct = $orderProduct;
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

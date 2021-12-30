<?php

namespace Backend\Modules\Commerce\Domain\OrderVat;

use Backend\Modules\Commerce\Domain\Order\Order;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Money\Money;

/**
 * @ORM\Table(name="commerce_order_vats")
 * @ORM\Entity(repositoryClass="OrderVatRepository")
 */
class OrderVat
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Order\Order", inversedBy="vats")
     * @ORM\JoinColumn(name="orderId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private Order $order;

    /**
     * @ORM\Column(type="string")
     */
    private string $title;

    /**
     * @ORM\Embedded(class="\Money\Money", columnPrefix="total")
     */
    private Money $total;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $updatedAt;

    private function __construct(
        Order $order,
        string $title,
        Money $total
    ) {
        $this->order = $order;
        $this->title = $title;
        $this->total = $total;
    }

    public static function fromDataTransferObject(OrderVatDataTransferObject $dataTransferObject): OrderVat
    {
        if ($dataTransferObject->hasExistingOrderVat()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(OrderVatDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->order,
            $dataTransferObject->title,
            $dataTransferObject->total
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getTotal(): Money
    {
        return $this->total;
    }

    private static function update(OrderVatDataTransferObject $dataTransferObject): OrderVat
    {
        return $dataTransferObject->getOrderVatEntity();
    }

    public function getDataTransferObject(): OrderVatDataTransferObject
    {
        return new OrderVatDataTransferObject($this);
    }
}

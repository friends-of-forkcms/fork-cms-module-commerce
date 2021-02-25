<?php

namespace Backend\Modules\Commerce\Domain\OrderVat;

use Backend\Modules\Commerce\Domain\Order\Order;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="commerce_order_vats")
 * @ORM\Entity(repositoryClass="OrderVatRepository")
 * @ORM\HasLifecycleCallbacks
 */
class OrderVat
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
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Order\Order", inversedBy="vats")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $order;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $total;

    private function __construct(
        Order $order,
        string $title,
        float $total
    ) {
        $this->order = $order;
        $this->title = $title;
        $this->total = $total;
    }

    public static function fromDataTransferObject(OrderVatDataTransferObject $dataTransferObject)
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

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    private static function update(OrderVatDataTransferObject $dataTransferObject)
    {
        $order = $dataTransferObject->getOrderVatEntity();

        return $order;
    }

    public function getDataTransferObject(): OrderVatDataTransferObject
    {
        return new OrderVatDataTransferObject($this);
    }
}

<?php

namespace Backend\Modules\Catalog\Domain\Order;

use Backend\Modules\Catalog\Domain\Product\Product;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_orders_values")
 * @ORM\Entity(repositoryClass="OrderValueRepository")
 * @ORM\HasLifecycleCallbacks
 */
class OrderValue
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
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="values")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $order;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="values")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $product;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", length=11, nullable=true)
     */
    private $amount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="date")
     */
    private $date;

    private function __construct(
        Order $order,
        Product $product,
        int $amount,
        \DateTime $date
    ) {
        $this->order   = $order;
        $this->product = $product;
        $this->amount  = $amount;
        $this->date    = $date;
    }

    public static function fromDataTransferObject(OrderDataTransferObject $dataTransferObject)
    {
        if ($dataTransferObject->hasExistingOrder()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(OrderDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->order
        );
    }

    private static function update(OrderDataTransferObject $dataTransferObject)
    {
        $order = $dataTransferObject->getOrderEntity();

        return $order;
    }

    public function getDataTransferObject(): OrderDataTransferObject
    {
        return new OrderDataTransferObject($this);
    }
}

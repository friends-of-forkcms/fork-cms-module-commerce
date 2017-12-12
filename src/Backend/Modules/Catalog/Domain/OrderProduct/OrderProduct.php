<?php

namespace Backend\Modules\Catalog\Domain\OrderProduct;

use Backend\Modules\Catalog\Domain\Order\Order;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_order_products")
 * @ORM\Entity(repositoryClass="OrderProductRepository")
 * @ORM\HasLifecycleCallbacks
 */
class OrderProduct
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
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\Order\Order", inversedBy="products")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $order;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $sku;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", length=11, nullable=true)
     */
    private $amount;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $price;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $total;

    private function __construct(
        Order $order,
        string $sku,
        string $title,
        int $amount,
        float $price,
        float $total
    ) {
        $this->order  = $order;
        $this->sku    = $sku;
        $this->title  = $title;
        $this->amount = $amount;
        $this->price  = $price;
        $this->total  = $total;
    }

    public static function fromDataTransferObject(OrderProductDataTransferObject $dataTransferObject)
    {
        if ($dataTransferObject->hasExistingOrderProduct()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(OrderProductDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->order,
            $dataTransferObject->sku,
            $dataTransferObject->title,
            $dataTransferObject->amount,
            $dataTransferObject->price,
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
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    private static function update(OrderProductDataTransferObject $dataTransferObject)
    {
        $order = $dataTransferObject->getOrderProductEntity();

        return $order;
    }

    public function getDataTransferObject(): OrderProductDataTransferObject
    {
        return new OrderProductDataTransferObject($this);
    }
}

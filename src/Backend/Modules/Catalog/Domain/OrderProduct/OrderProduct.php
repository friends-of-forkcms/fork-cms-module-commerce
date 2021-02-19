<?php

namespace Backend\Modules\Catalog\Domain\OrderProduct;

use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\OrderProductNotification\OrderProductNotification;
use Backend\Modules\Catalog\Domain\OrderProductOption\OrderProductOption;
use Backend\Modules\Catalog\Domain\Product\Product;
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
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\Product\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $product;

    /**
     * @var OrderProductOption[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\OrderProductOption\OrderProductOption", mappedBy="order_product", cascade={"remove", "persist"})
     */
    private $product_options;

    /**
     * @var OrderProductNotification[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\OrderProductNotification\OrderProductNotification", mappedBy="order_product", cascade={"remove", "persist"})
     */
    private $product_notifications;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"default" : 1})
     */
    private $type;

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
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $width;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $height;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $order_width;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $order_height;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $total;

    private function __construct(
        Order $order,
        ?Product $product,
        int $type,
        string $sku,
        string $title,
        ?int $width,
        ?int $height,
        ?int $order_width,
        ?int $order_height,
        int $amount,
        float $price,
        float $total,
        $product_options,
        $product_notifications
    )
    {
        $this->order = $order;
        $this->product = $product;
        $this->type = $type;
        $this->sku = $sku;
        $this->title = $title;
        $this->width = $width;
        $this->height = $height;
        $this->order_width = $order_width;
        $this->order_height = $order_height;
        $this->amount = $amount;
        $this->price = $price;
        $this->total = $total;
        $this->product_options = $product_options;
        $this->product_notifications = $product_notifications;
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
            $dataTransferObject->product,
            $dataTransferObject->type,
            $dataTransferObject->sku,
            $dataTransferObject->title,
            $dataTransferObject->width,
            $dataTransferObject->height,
            $dataTransferObject->order_width,
            $dataTransferObject->order_height,
            $dataTransferObject->amount,
            $dataTransferObject->price,
            $dataTransferObject->total,
            $dataTransferObject->productOptions,
            $dataTransferObject->productNotifications
        );
    }

    private static function update(OrderProductDataTransferObject $dataTransferObject)
    {
        return $dataTransferObject->getOrderProductEntity();
    }

    public function getDataTransferObject(): OrderProductDataTransferObject
    {
        return new OrderProductDataTransferObject($this);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Product
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @return OrderProductOption[]
     */
    public function getProductOptions()
    {
        return $this->product_options;
    }

    /**
     * @return OrderProductNotification[]
     */
    public function getProductNotifications()
    {
        return $this->product_notifications;
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
     * @return int
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @return float
     */
    public function getOrderWidth(): ?int
    {
        return $this->order_width;
    }

    /**
     * @return float
     */
    public function getOrderHeight(): ?int
    {
        return $this->order_height;
    }

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    public function hasDimensions(): bool
    {
        return $this->type == Product::TYPE_DIMENSIONS;
    }
}

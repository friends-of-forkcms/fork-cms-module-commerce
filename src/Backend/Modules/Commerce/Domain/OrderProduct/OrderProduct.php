<?php

namespace Backend\Modules\Commerce\Domain\OrderProduct;

use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\OrderProductNotification\OrderProductNotification;
use Backend\Modules\Commerce\Domain\OrderProductOption\OrderProductOption;
use Backend\Modules\Commerce\Domain\Product\Product;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="commerce_order_products")
 * @ORM\Entity(repositoryClass="OrderProductRepository")
 * @ORM\HasLifecycleCallbacks
 */
class OrderProduct
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Order\Order", inversedBy="products")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private Order $order;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Product\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?Product $product;

    /**
     * @var Collection|OrderProductOption[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\OrderProductOption\OrderProductOption", mappedBy="order_product", cascade={"remove", "persist"})
     */
    private Collection $product_options;

    /**
     * @var Collection|OrderProductNotification[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\OrderProductNotification\OrderProductNotification", mappedBy="order_product", cascade={"remove", "persist"})
     */
    private Collection $product_notifications;

    /**
     * @ORM\Column(type="integer", options={"default": 1})
     */
    private int $type;

    /**
     * @ORM\Column(type="string")
     */
    private string $sku;

    /**
     * @ORM\Column(type="string")
     */
    private string $title;

    /**
     * @ORM\Column(type="integer", length=11, nullable=true)
     */
    private ?int $amount;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private float $price;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $width;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $height;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $order_width;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $order_height;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private float $total;

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
    ) {
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

    public static function fromDataTransferObject(OrderProductDataTransferObject $dataTransferObject): OrderProduct
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

    private static function update(OrderProductDataTransferObject $dataTransferObject): OrderProduct
    {
        return $dataTransferObject->getOrderProductEntity();
    }

    public function getDataTransferObject(): OrderProductDataTransferObject
    {
        return new OrderProductDataTransferObject($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @return Collection|OrderProductOption[]
     */
    public function getProductOptions(): Collection
    {
        return $this->product_options;
    }

    /**
     * @return Collection|OrderProductNotification[]
     */
    public function getProductNotifications(): Collection
    {
        return $this->product_notifications;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function getOrderWidth(): ?int
    {
        return $this->order_width;
    }

    public function getOrderHeight(): ?int
    {
        return $this->order_height;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function hasDimensions(): bool
    {
        return $this->type === Product::TYPE_DIMENSIONS;
    }
}

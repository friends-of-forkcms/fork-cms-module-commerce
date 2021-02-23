<?php

namespace Backend\Modules\Commerce\Domain\OrderRule;

use Backend\Modules\Commerce\Domain\CartRule\CartRule;
use Backend\Modules\Commerce\Domain\Order\Order;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="commerce_order_rules")
 * @ORM\Entity(repositoryClass="OrderRuleRepository")
 * @ORM\HasLifecycleCallbacks
 */
class OrderRule
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
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Order\Order", inversedBy="rules")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $order;

    /**
     * @var CartRule
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\CartRule\CartRule")
     * @ORM\JoinColumn(name="cart_rule_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $cart_rule;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $total;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $value;

    private function __construct(
        Order $order,
        ?CartRule $cartRule,
        float $total,
        string $title,
        string $code,
        string $value
    ) {
        $this->order = $order;
        $this->cart_rule = $cartRule;
        $this->total = $total;
        $this->title = $title;
        $this->code = $code;
        $this->value = $value;
    }

    public static function fromDataTransferObject(OrderRuleDataTransferObject $dataTransferObject)
    {
        if ($dataTransferObject->hasExistingOrderRule()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(OrderRuleDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->order,
            $dataTransferObject->cartRule,
            $dataTransferObject->total,
            $dataTransferObject->title,
            $dataTransferObject->code,
            $dataTransferObject->value
        );
    }

    private static function update(OrderRuleDataTransferObject $dataTransferObject)
    {
        return $dataTransferObject->getOrderRuleEntity();
    }

    public function getDataTransferObject(): OrderRuleDataTransferObject
    {
        return new OrderRuleDataTransferObject($this);
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
     * @return CartRule
     */
    public function getCartRule(): ?CartRule
    {
        return $this->cart_rule;
    }

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}

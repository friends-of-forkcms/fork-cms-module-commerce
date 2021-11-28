<?php

namespace Backend\Modules\Commerce\Domain\OrderRule;

use Backend\Modules\Commerce\Domain\CartRule\CartRule;
use Backend\Modules\Commerce\Domain\Order\Order;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Money\Money;

/**
 * @ORM\Table(name="commerce_order_rules")
 * @ORM\Entity(repositoryClass="OrderRuleRepository")
 * @ORM\HasLifecycleCallbacks
 */
class OrderRule
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Order\Order", inversedBy="rules")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private Order $order;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\CartRule\CartRule")
     * @ORM\JoinColumn(name="cart_rule_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?CartRule $cart_rule;

    /**
     * @ORM\Embedded(class="\Money\Money")
     */
    private Money $total;

    /**
     * @ORM\Column(type="string")
     */
    private string $title;

    /**
     * @ORM\Column(type="string")
     */
    private string $code;

    /**
     * @ORM\Column(type="string")
     */
    private string $value;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="created_on", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdOn;

    private function __construct(
        Order $order,
        ?CartRule $cartRule,
        Money $total,
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

    public static function fromDataTransferObject(OrderRuleDataTransferObject $dataTransferObject): OrderRule
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

    private static function update(OrderRuleDataTransferObject $dataTransferObject): OrderRule
    {
        return $dataTransferObject->getOrderRuleEntity();
    }

    public function getDataTransferObject(): OrderRuleDataTransferObject
    {
        return new OrderRuleDataTransferObject($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getCartRule(): ?CartRule
    {
        return $this->cart_rule;
    }

    public function getTotal(): Money
    {
        return $this->total;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}

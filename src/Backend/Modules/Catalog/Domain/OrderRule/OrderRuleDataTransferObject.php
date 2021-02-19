<?php

namespace Backend\Modules\Catalog\Domain\OrderRule;

use Backend\Modules\Catalog\Domain\CartRule\CartRule;
use Backend\Modules\Catalog\Domain\Order\Order;
use Symfony\Component\Validator\Constraints as Assert;

class OrderRuleDataTransferObject
{
    /**
     * @var OrderRule
     */
    protected $orderRuleEntity;

    /**
     * @var int
     */
    public $id;

    /**
     * @var Order
     */
    public $order;

    /**
     * @var CartRule
     */
    public $cartRule;

    /**
     * @var float
     */
    public $total;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $value;

    public function __construct(OrderRule $orderRule = null)
    {
        $this->orderRuleEntity = $orderRule;

        if (!$this->hasExistingOrderRule()) {
            return;
        }

        $this->id = $orderRule->getId();
        $this->order = $orderRule->getOrder();
        $this->cartRule = $orderRule->getCartRule();
        $this->total = $orderRule->getTotal();
        $this->title = $orderRule->getTitle();
        $this->code = $orderRule->getCode();
        $this->value = $orderRule->getValue();
    }

    public function setOrderRuleEntity(OrderRule $orderRuleEntity): void
    {
        $this->orderRuleEntity = $orderRuleEntity;
    }

    public function getOrderRuleEntity(): OrderRule
    {
        return $this->orderRuleEntity;
    }

    public function hasExistingOrderRule(): bool
    {
        return $this->orderRuleEntity instanceof OrderRule;
    }
}

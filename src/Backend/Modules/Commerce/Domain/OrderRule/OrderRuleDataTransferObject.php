<?php

namespace Backend\Modules\Commerce\Domain\OrderRule;

use Backend\Modules\Commerce\Domain\CartRule\CartRule;
use Backend\Modules\Commerce\Domain\Order\Order;
use Money\Money;

class OrderRuleDataTransferObject
{
    protected ?OrderRule $orderRuleEntity = null;
    public int $id;
    public Order $order;
    public ?CartRule $cartRule = null;
    public Money $total;
    public string $title;
    public string $code;
    public string $value;

    public function __construct(OrderRule $orderRule = null)
    {
        $this->orderRuleEntity = $orderRule;

        if (!$this->hasExistingOrderRule()) {
            return;
        }

        $this->id = $this->orderRuleEntity->getId();
        $this->order = $this->orderRuleEntity->getOrder();
        $this->cartRule = $this->orderRuleEntity->getCartRule();
        $this->total = $this->orderRuleEntity->getTotal();
        $this->title = $this->orderRuleEntity->getTitle();
        $this->code = $this->orderRuleEntity->getCode();
        $this->value = $this->orderRuleEntity->getValue();
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

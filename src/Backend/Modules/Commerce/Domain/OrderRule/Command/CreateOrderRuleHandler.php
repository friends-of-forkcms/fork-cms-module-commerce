<?php

namespace Backend\Modules\Commerce\Domain\OrderRule\Command;

use Backend\Modules\Commerce\Domain\OrderRule\OrderRule;
use Backend\Modules\Commerce\Domain\OrderRule\OrderRuleRepository;

final class CreateOrderRuleHandler
{
    private OrderRuleRepository $orderRuleRepository;

    public function __construct(OrderRuleRepository $orderRuleRepository)
    {
        $this->orderRuleRepository = $orderRuleRepository;
    }

    public function handle(CreateOrderRule $createOrderRule): void
    {
        $orderRule = OrderRule::fromDataTransferObject($createOrderRule);

        $this->orderRuleRepository->add($orderRule);

        $createOrderRule->setOrderRuleEntity($orderRule);
    }
}

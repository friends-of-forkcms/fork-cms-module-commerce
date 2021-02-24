<?php

namespace Backend\Modules\Commerce\Domain\OrderRule\Command;

use Backend\Modules\Commerce\Domain\OrderRule\OrderRule;
use Backend\Modules\Commerce\Domain\OrderRule\OrderRuleRepository;

final class UpdateOrderRuleHandler
{
    private OrderRuleRepository $orderRuleRepository;

    public function __construct(OrderRuleRepository $orderRuleRepository)
    {
        $this->orderRuleRepository = $orderRuleRepository;
    }

    public function handle(UpdateOrderRule $updateOrderRule): void
    {
        $orderRule = OrderRule::fromDataTransferObject($updateOrderRule);
        $this->orderRuleRepository->add($orderRule);

        $updateOrderRule->setOrderRuleEntity($orderRule);
    }
}

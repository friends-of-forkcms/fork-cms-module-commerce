<?php

namespace Backend\Modules\Catalog\Domain\OrderRule\Command;

use Backend\Modules\Catalog\Domain\OrderRule\OrderRule;
use Backend\Modules\Catalog\Domain\OrderRule\OrderRuleRepository;

final class UpdateOrderRuleHandler
{
    /** @var OrderRuleRepository */
    private $orderRuleRepository;

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

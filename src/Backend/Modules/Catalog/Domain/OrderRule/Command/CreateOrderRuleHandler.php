<?php

namespace Backend\Modules\Catalog\Domain\OrderRule\Command;

use Backend\Modules\Catalog\Domain\OrderRule\OrderRule;
use Backend\Modules\Catalog\Domain\OrderRule\OrderRuleRepository;

final class CreateOrderRuleHandler
{
    /** @var OrderRuleRepository */
    private $orderRuleRepository;

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

<?php

namespace Backend\Modules\Commerce\Domain\OrderRule\Command;

use Backend\Modules\Commerce\Domain\OrderRule\OrderRuleRepository;

final class DeleteOrderRuleHandler
{
    /** @var OrderRuleRepository */
    private $orderRuleRepository;

    public function __construct(OrderRuleRepository $orderRuleRepository)
    {
        $this->orderRuleRepository = $orderRuleRepository;
    }

    public function handle(DeleteOrderRule $deleteOrderRule): void
    {
        $this->orderRuleRepository->removeById(
            $deleteOrderRule->orderRule->getId()
        );
    }
}

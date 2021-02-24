<?php

namespace Backend\Modules\Commerce\Domain\CartRule\Command;

use Backend\Modules\Commerce\Domain\CartRule\CartRule;
use Backend\Modules\Commerce\Domain\CartRule\CartRuleRepository;

final class UpdateCartRuleHandler
{
    private CartRuleRepository $cartRuleRepository;

    public function __construct(CartRuleRepository $cartRuleRepository)
    {
        $this->cartRuleRepository = $cartRuleRepository;
    }

    public function handle(UpdateCartRule $updateCartRule): void
    {
        $cartRule = CartRule::fromDataTransferObject($updateCartRule);
        $this->cartRuleRepository->add($cartRule);

        $updateCartRule->setCartRuleEntity($cartRule);
    }
}

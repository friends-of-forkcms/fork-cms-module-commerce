<?php

namespace Backend\Modules\Catalog\Domain\CartRule\Command;

use Backend\Modules\Catalog\Domain\CartRule\CartRule;
use Backend\Modules\Catalog\Domain\CartRule\CartRuleRepository;

final class UpdateCartRuleHandler
{
    /** @var CartRuleRepository */
    private $cartRuleRepository;

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

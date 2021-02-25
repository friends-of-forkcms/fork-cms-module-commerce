<?php

namespace Backend\Modules\Commerce\Domain\CartRule\Command;

use Backend\Modules\Commerce\Domain\CartRule\CartRuleRepository;

final class DeleteCartRuleHandler
{
    /** @var CartRuleRepository */
    private $cartRuleRepository;

    public function __construct(CartRuleRepository $cartRuleRepository)
    {
        $this->cartRuleRepository = $cartRuleRepository;
    }

    public function handle(DeleteCartRule $deleteCartRule): void
    {
        $this->cartRuleRepository->remove($deleteCartRule->cartRule);
    }
}

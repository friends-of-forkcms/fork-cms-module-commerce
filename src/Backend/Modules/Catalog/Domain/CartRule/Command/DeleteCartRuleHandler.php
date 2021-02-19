<?php

namespace Backend\Modules\Catalog\Domain\CartRule\Command;

use Backend\Modules\Catalog\Domain\CartRule\CartRuleRepository;

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

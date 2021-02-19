<?php

namespace Backend\Modules\Catalog\Domain\CartRule\Command;

use Backend\Modules\Catalog\Domain\CartRule\CartRule;
use Backend\Modules\Catalog\Domain\CartRule\CartRuleRepository;

final class CreateCartRuleHandler
{
    /** @var CartRuleRepository */
    private $cartRuleRepository;

    public function __construct(CartRuleRepository $cartRuleRepository)
    {
        $this->cartRuleRepository = $cartRuleRepository;
    }

    public function handle(CreateCartRule $createCartRule): void
    {
        if (!$createCartRule->code) {
            $createCartRule->code = $this->generateToken();
        }

        $cartRule = CartRule::fromDataTransferObject($createCartRule);
        $this->cartRuleRepository->add($cartRule);

        $createCartRule->setCartRuleEntity($cartRule);
    }

    public function generateToken()
    {
        return strtoupper(rtrim(strtr(base64_encode(random_bytes(5)), '+/', '-_'), '='));
    }
}

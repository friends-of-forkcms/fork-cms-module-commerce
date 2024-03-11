<?php

namespace Frontend\Modules\Commerce\Ajax;

use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\Cart\CartRepository;
use Backend\Modules\Commerce\Domain\CartRule\CartRule;
use Backend\Modules\Commerce\Domain\CartRule\CartRuleRepository;
use Common\Core\Cookie;
use Frontend\Core\Engine\Base\AjaxAction as FrontendBaseAJAXAction;
use Frontend\Core\Language\Language;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;
use Symfony\Component\HttpFoundation\Response;

class AddCartRule extends FrontendBaseAJAXAction
{
    private Cookie $cookie;
    private Cart $cart;
    private ?CartRule $cartRule;

    public function execute(): void
    {
        parent::execute();

        $this->cookie = $this->get('fork.cookie');
        $this->cart = $this->getActiveCart();
        $code = $this->getRequest()->request->get('code');

        // Cart rule code must be set
        if (!$this->getRequest()->request->has('code') || empty($code)) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY, null, Language::err('FieldIsRequired'));

            return;
        }

        // Discount code must exist
        if (!$this->cartRule = $this->getCartRuleRepository()->findValidByCode($code)) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY, null, Language::err('DiscountCodeNotFound'));

            return;
        }

        // Only add the cart rule when it does not exist in the current cart yet
        $cartRuleExists = $this->cart->getCartRules()->exists(fn ($key, $value) => $value->getId() === $this->cartRule->getId());
        if ($cartRuleExists) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY, null, Language::err('CartRuleAlreadyUsed'));

            return;
        }

        // Cart must meet minimum price requirement
        $this->cart->calculateTotals();
        if ($this->cartRule->getMinimumPrice() > $this->cart->getTotal()) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY, null, Language::err('MinimumAmountNotMet'));

            return;
        }

        // Discount code quantity must not be exceeded (when set)
        if ($this->cartRule->getQuantity() > 0 && $this->cartRule->getQuantity() <= $this->getCartRepository()->getCartRuleUsage($this->cartRule->getId())) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY, null, Language::err('ThisCartRuleIsNotValid'));

            return;
        }

        $this->cart->addCartRule($this->cartRule);
        $this->getCartRepository()->save($this->cart);

        $this->output(Response::HTTP_OK, ['cart' => $this->cart]);
    }

    /**
     * Get the active cart from the session.
     */
    private function getActiveCart(): Cart
    {
        $cartRepository = $this->getCartRepository();

        if (!$cartHash = $this->cookie->get('cart_hash')) {
            $cartHash = Uuid::uuid4();
            $this->cookie->set(
                'cart_hash',
                $cartHash,
                2592000,
                '/',
                null,
                true,
                true,
                false,
                SymfonyCookie::SAMESITE_NONE
            );
        }

        return $cartRepository->findBySessionId($cartHash, $this->getRequest()->getClientIp());
    }

    private function getCartRuleRepository(): CartRuleRepository
    {
        return $this->get('commerce.repository.cart_rule');
    }

    private function getCartRepository(): CartRepository
    {
        return $this->get('commerce.repository.cart');
    }
}

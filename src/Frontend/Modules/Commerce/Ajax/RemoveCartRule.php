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

class RemoveCartRule extends FrontendBaseAJAXAction
{
    private Cookie $cookie;
    private Cart $cart;
    private ?CartRule $cartRule;

    public function execute(): void
    {
        parent::execute();

        $this->cookie = $this->get('fork.cookie');
        $this->cart = $this->getActiveCart();
        $cartRuleId = $this->getRequest()->request->get('cartRuleId');

        // Cart rule id must be set
        if (!$this->getRequest()->request->has('cartRuleId') || empty($cartRuleId)) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY, null, Language::err('FieldIsRequired'));

            return;
        }

        // Find the cart rule in the DB
        if (!$this->cartRule = $this->getCartRuleRepository()->findOneById($cartRuleId)) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY, null, Language::err('DiscountCodeNotFound'));

            return;
        }

        // Only remove the cart rule when it exists
        $cartRuleExists = $this->cart->getCartRules()->exists(fn ($key, $value) => $value->getId() === $this->cartRule->getId());
        if (!$cartRuleExists) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY, null, Language::err('DiscountCodeNotFound'));

            return;
        }

        $this->cart->removeCartRule($this->cartRule);
        $this->getCartRepository()->save($this->cart);

        // Return everything
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
                null,
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

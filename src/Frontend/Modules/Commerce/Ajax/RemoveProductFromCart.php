<?php

namespace Frontend\Modules\Commerce\Ajax;

use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\Cart\CartRepository;
use Backend\Modules\Commerce\Domain\Cart\CartValue;
use Backend\Modules\Commerce\Domain\Cart\CartValueRepository;
use Backend\Modules\Commerce\Domain\Product\Product;
use Common\Core\Cookie;
use Frontend\Core\Engine\Base\AjaxAction as FrontendBaseAJAXAction;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;
use Symfony\Component\HttpFoundation\Response;

class RemoveProductFromCart extends FrontendBaseAJAXAction
{
    private Cookie $cookie;
    private Cart $cart;

    public function execute(): void
    {
        parent::execute();

        $this->cookie = $this->get('fork.cookie');
        $this->cart = $this->getActiveCart();

        // Product must be set
        if (!$this->getRequest()->request->has('cart')) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY);

            return;
        }

        // Failed to update product, it does not exist in the cart
        if (!$cartValue = $this->removeCartValue()) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY);

            return;
        }

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

    /**
     * Remove the product in our cart.
     */
    private function removeCartValue(): ?CartValue
    {
        $cartData = $this->getRequest()->request->get('cart');

        if (!is_array($cartData)) {
            return null;
        }

        if (!array_key_exists('value_id', $cartData)) {
            return null;
        }

        // Retrieve our product
        $cartValueRepository = $this->getCartValueRepository();
        if (!$cartValue = $cartValueRepository->getByCartAndId($this->cart, $cartData['value_id'])) {
            return null;
        }

        // Remove the value
        $this->cart->removeValue($cartValue);
        $cartValueRepository->removeByIdAndCart($cartValue->getId(), $this->cart);

        return $cartValue;
    }

    private function getCartRepository(): CartRepository
    {
        return $this->get('commerce.repository.cart');
    }

    private function getCartValueRepository(): CartValueRepository
    {
        return $this->get('commerce.repository.cart_value');
    }
}

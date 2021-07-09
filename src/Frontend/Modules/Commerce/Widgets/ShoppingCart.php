<?php

namespace Frontend\Modules\Commerce\Widgets;

use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\Cart\CartRepository;
use Common\Core\Cookie;
use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;

/**
 * This is a widget for the shopping cart.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class ShoppingCart extends FrontendBaseWidget
{
    private Cookie $cookie;

    public function execute(): void
    {
        parent::execute();
        $this->loadTemplate();

        $this->cookie = $this->get('fork.cookie');

        $this->template->assign('cart', $this->getActiveCart());
    }

    /**
     * Get the active cart from the session.
     */
    private function getActiveCart(): Cart
    {
        $cartRepository = $this->getCartRepository();

        if (!$cartHash = $this->cookie->get('cart_hash')) {
            return new Cart();
        }

        return $cartRepository->findBySessionId($cartHash, $this->getRequest()->getClientIp());
    }

    private function getCartRepository(): CartRepository
    {
        return $this->get('commerce.repository.cart');
    }
}

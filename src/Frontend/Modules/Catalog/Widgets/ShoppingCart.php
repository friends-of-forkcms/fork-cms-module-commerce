<?php

namespace Frontend\Modules\Catalog\Widgets;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Modules\Catalog\Domain\Cart\Cart;
use Backend\Modules\Catalog\Domain\Cart\CartRepository;
use Common\Core\Cookie;
use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;

/**
 * This is a widget for the shopping cart
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class ShoppingCart extends FrontendBaseWidget
{
    /**
     * @var Cookie
     */
    private $cookie;

    /**
     * Execute the extra
     */
    public function execute(): void
    {
        parent::execute();
        $this->loadTemplate();

        $this->cookie = $this->get('fork.cookie');

        $this->template->assign('cart', $this->getActiveCart());
    }

    /**
     * Get the active cart from the session
     *
     * @return Cart
     */
    private function getActiveCart(): Cart
    {
        $cartRepository = $this->getCartRepository();

        if (!$cartHash = $this->cookie->get('cart_hash')) {
            return new Cart();
        }

        return $cartRepository->findBySessionId($cartHash, $this->getRequest()->getClientIp());
    }

    /**
     * Get the cart repository
     *
     * @return CartRepository
     */
    private function getCartRepository(): CartRepository
    {
        return $this->get('catalog.repository.cart');
    }
}

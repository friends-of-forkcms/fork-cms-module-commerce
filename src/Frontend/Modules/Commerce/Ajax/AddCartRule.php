<?php

namespace Frontend\Modules\Commerce\Ajax;

use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\Cart\CartRepository;
use Backend\Modules\Commerce\Domain\CartRule\CartRule;
use Backend\Modules\Commerce\Domain\CartRule\CartRuleRepository;
use Common\Core\Cookie;
use Frontend\Core\Engine\Base\AjaxAction as FrontendBaseAJAXAction;
use Frontend\Core\Engine\TemplateModifiers;
use Frontend\Core\Language\Language;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;
use Symfony\Component\HttpFoundation\Response;

class AddCartRule extends FrontendBaseAJAXAction
{
    /**
     * @var Cookie
     */
    private $cookie;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var CartRule
     */
    private $cartRule;

    /**
     * {@inheritdoc}
     */
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

        if (!$this->cartRule = $this->getCartRuleRepository()->findValidByCode($code)) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY, null, Language::err('DiscountCodeNotFound'));
            return;
        }

        if ($this->cartRule->getQuantity() < 1) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY, null, Language::err('ThisCartRuleIsNotValid'));
            return;
        }

        // Only add the cart rule when it doesn't exists
        $cartRuleExists = $this->cart->getCartRules()->exists(function($key, $value){
            return $value->getId() == $this->cartRule->getId();
        });

        if (!$cartRuleExists) {
            $this->cart->addCartRule($this->cartRule);
            $this->getCartRepository()->save($this->cart);
        }

        // Return everything
        $this->output(
            Response::HTTP_OK,
            [
                'totals' => $this->getCartTotals(),
            ]
        );
    }

    private function getCartTotals()
    {
        $shippingMethod = $this->cart->getShipmentMethodData();
        $vats = [];
        $cartRules = [];

        foreach($this->cart->getVats() as $vat) {
            $vats[] = [
                'title' => $vat['title'],
                'total' => TemplateModifiers::formatNumber($vat['total'], 2),
            ];
        }

        foreach ($this->cart->getCartRules() as $cartRule) {
            if ($cartRule->getReductionPercentage()) {
                $total = $cartRule->getReductionPercentage() .'% ' . Language::lbl('discount');
            } else {
                $total = '- &euro;' . TemplateModifiers::formatNumber($cartRule->getReductionAmount(), 2);
            }

            $cartRules[] = [
                'code' => $cartRule->getCode(),
                'title' => $cartRule->getTitle(),
                'total' => $total,
            ];
        }

        return [
            'sub_total' =>  TemplateModifiers::formatNumber($this->cart->getSubTotal(), 2),
            'vats' => $vats,
            'shipping_method' => [
                'name' => $shippingMethod['name'],
                'price' => TemplateModifiers::formatNumber($shippingMethod['price'], 2),
            ],
            'total' =>  TemplateModifiers::formatNumber($this->cart->getTotal(), 2),
            'cart_rules' => $cartRules,
        ];
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

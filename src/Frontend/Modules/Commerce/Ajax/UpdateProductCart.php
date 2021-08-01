<?php

namespace Frontend\Modules\Commerce\Ajax;

use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\Cart\CartRepository;
use Backend\Modules\Commerce\Domain\Cart\CartValue;
use Backend\Modules\Commerce\Domain\Cart\CartValueRepository;
use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\Product\ProductRepository;
use Common\Core\Cookie;
use Frontend\Core\Engine\Base\AjaxAction as FrontendBaseAJAXAction;
use Frontend\Core\Engine\TemplateModifiers;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;
use Symfony\Component\HttpFoundation\Response;

class UpdateProductCart extends FrontendBaseAJAXAction
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
     * @var string
     */
    private $error;

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        parent::execute();

        $this->cookie = $this->get('fork.cookie');
        $this->cart = $this->getActiveCart();
        $moneyFormatter = new DecimalMoneyFormatter(new ISOCurrencies());

        // Cart id must be set
        if (!$this->getRequest()->request->has('cartValueId')) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY);

            return;
        }

        // Amount must be set and valid
        if (!$this->getRequest()->request->has('amount')) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY);

            return;
        }

        // Failed to update product, does not exist?
        if (!$cartValue = $this->updateProductCart()) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY, ['error' => $this->error]);

            return;
        }

        $this->getCartRepository()->save($this->cart);

        $this->output(
            Response::HTTP_OK,
            [
                'cart' => [
                    'totalQuantity' => $this->cart->getTotalQuantity(),
                    'subTotal' => $moneyFormatter->format($this->cart->getSubTotal()),
                    'total' => $moneyFormatter->format($this->cart->getTotal()),
                    'vats' => $this->getFormattedVats(),
                ],
                'product' => [
                    'sku' => $cartValue->getProduct()->getSku(),
                    'name' => $cartValue->getProduct()->getTitle(),
                    'category' => $this->buildEcommerceCategory($cartValue->getProduct()),
                    'brand' => $cartValue->getProduct()->getBrand()->getTitle(),
                    'quantity' => $cartValue->getQuantity(),
                    'total' => $moneyFormatter->format($cartValue->getTotal()),
                ],
            ]
        );
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

    /**
     * Add or update the product in our cart.
     */
    private function updateProductCart(): ?CartValue
    {
        $cartValueId = $this->getRequest()->request->getInt('cartValueId');

        if (!$amount = $this->getRequest()->request->getInt('amount')) {
            return null;
        }

        if ($amount < 1) {
            $amount = 1;
        }

        // Retrieve our product
        $cartValueRepository = $this->getCartValueRepository();
        if (!$cartValue = $cartValueRepository->getByCartAndId($this->cart, $cartValueId)) {
            return null;
        }

        $cartValue->setQuantity($amount);
        $cartValue->setTotal($cartValue->getPrice()->multiply($amount));
//        $cartValueRepository->

//        $cartValue->save

        // Remove the value
//        $this->cart->removeValue($cartValue);
//        $cartValueRepository->removeByIdAndCart($cartValue->getId(), $this->cart);

        return $cartValue;
    }

    /**
     * Get the cart repository.
     */
    private function getCartRepository(): CartRepository
    {
        return $this->get('commerce.repository.cart');
    }

    /**
     * Get the cart value repository.
     */
    private function getCartValueRepository(): CartValueRepository
    {
        return $this->get('commerce.repository.cart_value');
    }

    /**
     * Format the vats in an array with the required number format.
     */
    private function getFormattedVats(): array
    {
        $vats = $this->cart->getVats();
        $moneyFormatter = new DecimalMoneyFormatter(new ISOCurrencies());

        foreach ($vats as $key => $vat) {
            $vats[$key]['total'] = $moneyFormatter->format($vat['total']);
        }

        return $vats;
    }

    /**
     * Build the ecommerce category in required format.
     *
     * @return string
     */
    private function buildEcommerceCategory(Product $product)
    {
        $categories = [];
        $category = $product->getCategory();

        while ($category) {
            array_unshift($categories, $category->getTitle());
            $category = $category->getParent();
        }

        return implode('/', $categories);
    }
}

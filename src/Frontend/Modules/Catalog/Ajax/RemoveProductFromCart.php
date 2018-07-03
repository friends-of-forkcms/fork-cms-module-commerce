<?php

namespace Frontend\Modules\Catalog\Ajax;

use Backend\Modules\Catalog\Domain\Cart\Cart;
use Backend\Modules\Catalog\Domain\Cart\CartRepository;
use Backend\Modules\Catalog\Domain\Cart\CartValue;
use Backend\Modules\Catalog\Domain\Cart\CartValueRepository;
use Backend\Modules\Catalog\Domain\Product\Exception\ProductNotFound;
use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\Product\ProductRepository;
use Common\Core\Cookie;
use Frontend\Core\Engine\Base\AjaxAction as FrontendBaseAJAXAction;
use Frontend\Core\Engine\TemplateModifiers;
use Frontend\Core\Language\Locale;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class RemoveProductFromCart extends FrontendBaseAJAXAction
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
        $this->cart   = $this->getActiveCart();

        // Product must be set
        if (!$this->getRequest()->request->has('product')) {
            $this->output( Response::HTTP_UNPROCESSABLE_ENTITY);
            return;
        }

        // Failed to update product, does not exists?
        if (!$cartValue = $this->removeProduct()) {
            $this->output( Response::HTTP_UNPROCESSABLE_ENTITY, ['error' => $this->error]);
            return;
        }

        $this->getCartRepository()->save($this->cart);

        $this->output(
            Response::HTTP_OK,
            [
                'cart' => [
                    'totalQuantity' => $this->cart->getTotalQuantity(),
                    'subTotal' => TemplateModifiers::formatNumber($this->cart->getSubTotal(), 2),
                    'total' => TemplateModifiers::formatNumber($this->cart->getTotal(), 2),
                    'vats' => $this->getFormattedVats(),
                ],
                'product' => [
                    'sku' => $cartValue->getProduct()->getSku(),
                    'name' => $cartValue->getProduct()->getTitle(),
                    'category' => $this->buildEcommerceCategory($cartValue->getProduct()),
                    'brand' => $cartValue->getProduct()->getBrand()->getTitle(),
                    'quantity' => $cartValue->getQuantity(),
                    'total' => TemplateModifiers::formatNumber($cartValue->getTotal(), 2),
                ],
            ]
        );
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
            $this->cookie->set('cart_hash', $cartHash);
        }

        return $cartRepository->findBySessionId($cartHash, $this->getRequest()->getClientIp());
    }

    /**
     * Add or update the product in our cart
     *
     * @return CartValue|null
     */
    private function removeProduct(): ?CartValue
    {
        $productData = $this->getRequest()->request->get('product');

        if (!is_array($productData)) {
            return null;
        }

        if (!array_key_exists('id', $productData)) {
            return null;
        }

        // Retrieve our product
        try {
            $product = $this->getProductRepository()->findOneByIdAndLocale($productData['id'], Locale::frontendLanguage());
        } catch (ProductNotFound $e) {
            return null;
        }

        // Retrieve the cart value
        $cartValueRepository = $this->getCartValueRepository();
        $cartValue = $cartValueRepository->getByCartAndProduct($this->cart, $product);

        // Remove the value
        $this->cart->removeValue($cartValue);
        $cartValueRepository->removeByIdAndCart($cartValue->getId(), $this->cart);

        return $cartValue;
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

    /**
     * Get the cart value repository
     *
     * @return CartValueRepository
     */
    private function getCartValueRepository(): CartValueRepository
    {
        return $this->get('catalog.repository.cart_value');
    }

    /**
     * Get the product repository
     *
     * @return ProductRepository
     */
    private function getProductRepository(): ProductRepository
    {
        return $this->get('catalog.repository.product');
    }

    /**
     * Format the vats in an array with the required number format
     *
     * @return array
     */
    private function getFormattedVats(): array
    {
        $vats = $this->cart->getVats();

        foreach ($vats as $key => $vat) {
            $vats[$key]['total'] = TemplateModifiers::formatNumber($vat['total'], 2);
        }

        return $vats;
    }

    /**
     * Build the ecommerce category in required format
     *
     * @param Product $product
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

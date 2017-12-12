<?php

namespace Frontend\Modules\Catalog\Ajax;

use Backend\Modules\Catalog\Domain\Cart\Cart;
use Backend\Modules\Catalog\Domain\Cart\CartRepository;
use Backend\Modules\Catalog\Domain\Cart\CartValue;
use Backend\Modules\Catalog\Domain\Cart\CartValueRepository;
use Backend\Modules\Catalog\Domain\Product\ProductRepository;
use Common\Core\Cookie;
use Frontend\Core\Engine\Base\AjaxAction as FrontendBaseAJAXAction;
use Frontend\Core\Engine\Navigation;
use Frontend\Core\Engine\TemplateModifiers;
use Frontend\Core\Language\Language;
use Frontend\Core\Language\Locale;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class UpdateCart extends FrontendBaseAJAXAction
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
        if (!$cartValue = $this->updateProduct()) {
            $this->output( Response::HTTP_UNPROCESSABLE_ENTITY, ['error' => $this->error]);
            return;
        }

        $this->getCartRepository()->save($this->cart);

        // Force update of cart totals
        $this->cart->calculateTotals();

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
                    'total' => TemplateModifiers::formatNumber($cartValue->getTotal(), 2),
                ],
                'urls' => [
                    'cart' => Navigation::getUrlForBlock('Catalog', 'Cart'),
                    'request_quote' =>  Navigation::getUrlForBlock('Catalog', 'Cart') .'/'. Language::lbl('RequestQuoteUrl'),
                ]
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
     * @return CartValue|false
     */
    private function updateProduct()
    {
        $productData = $this->getRequest()->request->get('product');

        if (!is_array($productData)) {
            return false;
        }

        if (!array_key_exists('id', $productData) || !array_key_exists('amount', $productData)) {
            return false;
        }

        // Update or overwrite product
        $overwrite = false;
        if (array_key_exists('overwrite', $productData)) {
            $overwrite = $productData['overwrite'];
        }

        // Retrieve our product
        $product = $this->getProductRepository()->findOneByIdAndLocale($productData['id'], Locale::frontendLanguage());
        $amount = (int) $productData['amount'];

        // Disable amount below 0
        if ($amount <= 0) {
            $amount = 1;
        }

        // Validate request
        if ($product->isFromStock() && $amount > $product->getStock()) {
            $this->error = Language::err('GivenAmountNotInStock');
            return false;
        }
        // Get the cart value if exists or create a new one
        $cartValueRepository = $this->getCartValueRepository();
        $cartValue = $cartValueRepository->getByCartAndProduct($this->cart, $product);

        // Set the values
        if ($overwrite) {
            $cartValue->setQuantity($amount);
        } else {
            $cartValue->setQuantity($cartValue->getQuantity() + $amount);
        }

        $cartValue->setTotal($cartValue->getQuantity() * $product->getActivePrice(false));

        // Add our product to the cart
        $this->cart->addValue($cartValue);

        // Handle the product upsell
        if (array_key_exists('up_sell', $productData)) {
            $this->upSellProducts($productData['up_sell']);
        }

        return $cartValue;
    }

    /**
     * Up sell products
     *
     * @param array $products
     *
     * @param array
     */
    private function upSellProducts($products)
    {
        $productRepository   = $this->getProductRepository();
        $cartValueRepository = $this->getCartValueRepository();

        foreach ($products as $product) {
            $product = $productRepository->findOneByIdAndLocale($product, Locale::frontendLanguage());
            $cartValue = $cartValueRepository->getByCartAndProduct($this->cart, $product);

            // Set the values
            $cartValue->setQuantity($cartValue->getQuantity() + $product->getOrderQuantity());
            $cartValue->setTotal($cartValue->getQuantity() * $product->getActivePrice(false));

            // Add our product to the cart
            $this->cart->addValue($cartValue);

            if ($product->isFromStock()) {
                $product->setStock($product->getStock() - $product->getOrderQuantity());
            }
        }
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
}

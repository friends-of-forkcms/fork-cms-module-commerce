<?php

namespace Frontend\Modules\Catalog\Ajax;

use Backend\Modules\Catalog\Domain\Cart\Cart;
use Backend\Modules\Catalog\Domain\Cart\CartRepository;
use Backend\Modules\Catalog\Domain\Cart\CartValue;
use Backend\Modules\Catalog\Domain\Cart\CartValueOptionRepository;
use Backend\Modules\Catalog\Domain\Cart\CartValueRepository;
use Backend\Modules\Catalog\Domain\Product\AddToCartDataTransferObject;
use Backend\Modules\Catalog\Domain\Product\AddToCartType;
use Backend\Modules\Catalog\Domain\Product\Exception\ProductNotFound;
use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\Product\ProductRepository;
use Common\Core\Cookie;
use Doctrine\ORM\NonUniqueResultException;
use Frontend\Core\Engine\Base\AjaxAction as FrontendBaseAJAXAction;
use Frontend\Core\Engine\Navigation;
use Frontend\Core\Engine\TemplateModifiers;
use Frontend\Core\Language\Language;
use Frontend\Core\Language\Locale;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\SecurityBundle\Tests\Functional\Bundle\AclBundle\Entity\Car;
use Symfony\Component\Form\Form;
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
    private $errors;

    /**
     * @var Form
     */
    private $form;

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        parent::execute();

        $this->cookie = $this->get('fork.cookie');
        $this->cart = $this->getActiveCart();

        // Product must be set
        if (!$this->getRequest()->request->has('product')) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY);
            return;
        }

        // Failed to update product, does not exists?
        if (!$cartValue = $this->updateProduct()) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY, ['errors' => $this->errors]);
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
                    'sku' => $cartValue->getProduct()->getSku(),
                    'name' => $cartValue->getProduct()->getTitle(),
                    'category' => $this->buildEcommerceCategory($cartValue->getProduct()),
                    'brand' => $cartValue->getProduct()->getBrand()->getTitle(),
                    'quantity' => $cartValue->getQuantity(),
                    'total' => TemplateModifiers::formatNumber($cartValue->getTotal(), 2),
                    'options' => $this->getCartValueOptionTotals($cartValue),
                ],
                'urls' => [
                    'cart' => Navigation::getUrlForBlock('Catalog', 'Cart'),
                    'request_quote' => Navigation::getUrlForBlock('Catalog', 'Cart') . '/' . Language::lbl('RequestQuoteUrl'),
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
        try {
            $product = $this->getProductRepository()->findOneByIdAndLocale($productData['id'], Locale::frontendLanguage());
        } catch (ProductNotFound $e) {
            return false;
        }

        // Get the form on which we are going to validate our stuff
        $this->form = $this->getForm($product);
        $this->form->handleRequest($this->getRequest());

        if (!$this->form->isSubmitted() || !$this->form->isValid()) {
            $fields = [];
            foreach ($this->form->getErrors(true) as $error) {
                $fields[$error->getOrigin()->getName()] = $error->getMessage();
            }

            $this->errors = [
                'fields' => $fields,
                'url' => $product->getUrl(),
            ];

            return false;
        }

        /**
         * @var AddToCartDataTransferObject $formData
         */
        $formData = $this->form->getData();

        $amount = (int)$formData->amount;

        // Disable amount below the minimal order quantity
        if ($amount <= $product->getOrderQuantity()) {
            $amount = $product->getOrderQuantity();
        }

        // Validate request
        if (!array_key_exists('quote', $productData) && $product->isFromStock() && $amount > $product->getStock()) {
            $this->errors = [
                'fields' => [
                    'amount' => Language::err('GivenAmountNotInStock'),
                ],
            ];
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

        // Add the product options to the cart
        $this->addProductOptionsToCart($product, $cartValue);

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
        $productRepository = $this->getProductRepository();
        $cartValueRepository = $this->getCartValueRepository();

        foreach ($products as $product) {
            try {
                $product = $productRepository->findOneByIdAndLocale($product, Locale::frontendLanguage());
            } catch (ProductNotFound $e) {
                continue;
            }

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
     * Get the product form
     *
     * @param Product $product
     *
     * @return Form
     */
    private function getForm(Product $product): Form
    {
        return $this->get('form.factory')->create(
            AddToCartType::class,
            new AddToCartDataTransferObject($product),
            [
                'product' => $product,
            ]
        );
    }

    /**
     * Add the product options to the cart
     *
     * @param Product $product
     * @param CartValue $cartValue
     *
     * @return void
     */
    private function addProductOptionsToCart(Product $product, CartValue $cartValue): void
    {
        $repository = $this->getCartValueOptionRepository();
        $formData = $this->form->getData();

        foreach ($product->getProductOptions() as $productOption) {
            $fieldName = 'option_' . $productOption->getId();

            if ($formData->$fieldName) {
                try {
                    $cartValue->addCartValueOption(
                        $repository->getByCartValueAndProductOptionValue($cartValue, $formData->$fieldName)
                    );
                } catch (NonUniqueResultException $e) {
                    continue;
                }
            }
        }
    }

    /**
     * Get the cart value option totals for the given product
     *
     * @param CartValue $cartValue
     *
     * @return array
     */
    private function getCartValueOptionTotals(CartValue $cartValue): array
    {
        $totals = [];

        foreach ($cartValue->getCartValueOptions() as $cartValueOption) {
            $totals[$cartValueOption->getProductOptionValue()->getId()] = TemplateModifiers::formatNumber($cartValueOption->getTotal(), 2);
        }

        return $totals;
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
     * Get the cart value option repository
     *
     * @return CartValueOptionRepository
     */
    private function getCartValueOptionRepository(): CartValueOptionRepository
    {
        return $this->get('catalog.repository.cart_value_option');
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
}

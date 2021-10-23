<?php

namespace Frontend\Modules\Commerce\Ajax;

use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\Cart\CartRepository;
use Backend\Modules\Commerce\Domain\Cart\CartValue;
use Backend\Modules\Commerce\Domain\Cart\CartValueOption;
use Backend\Modules\Commerce\Domain\Cart\CartValueRepository;
use Backend\Modules\Commerce\Domain\Product\Exception\ProductNotFound;
use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;
use Common\Core\Cookie;
use Doctrine\ORM\NonUniqueResultException;
use Frontend\Core\Language\Language;
use Frontend\Core\Language\Locale;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * E.g. adding a product to the shopping cart triggers this action
 */
class UpdateCart extends DimensionCalculator
{
    private Cookie $cookie;
    private Cart $cart;

    /**
     * @var int
     */
    private $cartValueId;

    /**
     * @var string
     */
    private $errors;

    /**
     * @var Form
     */
    private $form;

    public function execute(): void
    {
        parent::execute();

        $this->cookie = $this->get('fork.cookie');
        $this->cart = $this->getActiveCart();
        $this->cartValueId = $this->getRequest()->request->get('cartId');

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

    /**
     * Add or update the product in our cart.
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
            $product = $this->getProductRepository()->findOneActiveByIdAndLocale($productData['id'], Locale::frontendLanguage());
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

        $this->data = $this->form->getData();

        // Disable amount below the minimal order quantity
        if ($this->data->amount <= $product->getOrderQuantity()) {
            $this->data->amount = $product->getOrderQuantity();
        }

        // Validate request
        if (!array_key_exists('quote', $productData) && $product->isFromStock() && $this->data->amount > $product->getStock()) {
            $this->errors = [
                'fields' => [
                    'amount' => Language::err('GivenAmountNotInStock'),
                ],
            ];

            return false;
        }

        // Get the cart value if exists or create a new one
        $cartValueRepository = $this->getCartValueRepository();
        if ($this->cartValueId) {
            $cartValue = $cartValueRepository->getByCartAndId($this->cart, $this->cartValueId);

            if (!$cartValue) {
                return false;
            }
        } elseif ($product->usesDimensions()) {
            $cartValue = new CartValue();
            $cartValue->setCart($this->cart);
            $cartValue->setProduct($product);
        } else {
            $cartValue = $cartValueRepository->getByCartAndProduct($this->cart, $product);
        }

        // Set the values
        if ($overwrite) {
            $cartValue->setQuantity($this->data->amount);
        } else {
            $cartValue->setQuantity($cartValue->getQuantity() + $this->data->amount);
        }

        // Choose which product to use and set some default price
        if ($product->usesDimensions()) {
            // Set the given dimensions
            $this->addWidth($this->data->width);
            $this->addHeight($this->data->height);

            // Add extra production dimensions
            $this->addWidth($product->getExtraProductionWidth());
            $this->addHeight($product->getExtraProductionHeight());

            // Parse any given dimensions
            $this->parseProductOptionsDimension($product->getProductOptions());

            $dimension = $this->getProductDimensionRepository()
                ->findByProductAndDimensions($product, $this->getWidth(), $this->getHeight());

            if (!$dimension) {
                $this->errors = [
                    'fields' => [
                        'width' => Language::err('CantFindProductWithGivenDimensions'),
                    ],
                ];

                return false;
            }

            $this->setBasePrice($dimension->getPrice());

            $cartValue->setProductDimension($dimension);
            $cartValue->setWidth($this->data->width);
            $cartValue->setHeight($this->data->height);
            $cartValue->setOrderWidth($this->getWidth());
            $cartValue->setOrderHeight($this->getHeight());
        } else {
            $this->setBasePrice($product->getActivePrice(false));
        }

        // Set the total price for later usage
        $this->addTotalPrice($this->getBasePrice());

        // Add the product options to the cart
        $this->addProductOptionsToCart($product, $cartValue);

        $cartValue->setTotal($this->getTotalPrice()->multiply($cartValue->getQuantity()));

        // Add our product to the cart
        $this->cart->addValue($cartValue);

        // Handle the product upsell
        if (array_key_exists('up_sell', $productData)) {
            $this->upSellProducts($productData['up_sell']);
        }

        return $cartValue;
    }

    /**
     * Up sell products.
     *
     * @param array $products
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
            $cartValue->setTotal($product->getActivePrice(false)->multiply($cartValue->getQuantity()));

            // Add our product to the cart
            $this->cart->addValue($cartValue);

            if ($product->isFromStock()) {
                $product->setStock($product->getStock() - $product->getOrderQuantity());
            }
        }
    }

    /**
     * Add the product options to the cart.
     */
    private function addProductOptionsToCart(Product $product, CartValue $cartValue): void
    {
        $this->data = $this->form->getData();

        foreach ($cartValue->getCartValueOptions() as $cartValueOption) {
            $cartValue->removeCartValueOption($cartValueOption);
        }

        foreach ($product->getProductOptionsWithSubOptions() as $productOption) {
            $fieldName = 'option_' . $productOption->getId();
            $customValueFieldName = $fieldName . '_custom_value';

            if ($this->data->{$fieldName} || isset($this->data->{$customValueFieldName})) {
                /**
                 * @var ProductOptionValue|string $productOptionValue
                 */
                $productOptionValue = $this->data->{$fieldName};

                try {
                    $cartValueOption = new CartValueOption();
                    $cartValueOption->setCartValue($cartValue);
                    $cartValueOption->setProductOption($productOption);
                    $cartValueOption->setName($productOption->getTitle());

                    if ($productOptionValue instanceof ProductOptionValue) {
                        $cartValueOption->setImpactType($productOptionValue->getImpactType());
                    }

                    if ($productOption->isCustomValueAllowed() && $this->data->{$customValueFieldName}) {
                        $cartValueOption->setValue($this->data->{$customValueFieldName});
                        $cartValueOption->setPrice($productOption->getCustomValuePrice());
                        $cartValueOption->setVat($product->getVat());
                        $cartValueOption->setVatPrice($productOption->getCustomValuePrice()->multiply($product->getVat()->getAsPercentage()));
                    } else {
                        switch ($productOption->getType()) {
                            case ProductOption::DISPLAY_TYPE_TEXT:
                                $value = $productOptionValue;

                                break;
                            case ProductOption::DISPLAY_TYPE_BETWEEN:
                                $value = $this->data->{$customValueFieldName};

                                break;
                            default:
                                $value = $productOptionValue->getTitle();

                                break;
                        }
                        $cartValueOption->setValue($value);
                        $cartValueOption->setVat($product->getVat());
                        $cartValueOption->setPrice(0);
                        $cartValueOption->setVatPrice(0);

                        if ($productOptionValue instanceof ProductOptionValue) {
                            $cartValueOption->setProductOptionValue($productOptionValue);
                            $cartValueOption->setVat($productOptionValue->getVat());

                            if ($productOptionValue->getPercentage() > 0) {
                                $productOptionValuePrice = $this->getBasePrice()->multiply(($productOptionValue->getPercentage() / 100));
                                $cartValueOption->setPrice($productOptionValuePrice);
                                $cartValueOption->setVatPrice($productOptionValuePrice->multiply($productOptionValue->getVat()->getAsPercentage()));
                            } else {
                                $cartValueOption->setPrice($productOptionValue->getPrice());
                                $cartValueOption->setVatPrice($productOptionValue->getVatPrice());
                            }
                        }
                    }

                    // Only do extra calculations based on square unit type
                    if ($productOption->isSquareUnitType() && $product->usesDimensions()) {
                        // @TODO assumed unit is given in MM
                        $square = ceil(($this->getWidth() / 100) * ($this->getHeight() / 100));

                        $cartValueOption->setPrice($cartValueOption->getPrice()->multiply($square));
                        $cartValueOption->setVatPrice($cartValueOption->getVatPrice()->multiply($square));
                    }

                    $cartValue->addCartValueOption($cartValueOption);

                    if (!$productOptionValue instanceof ProductOptionValue || $productOptionValue->isImpactTypeAdd()) {
                        $this->addTotalPrice($cartValueOption->getPrice());
                    } else {
                        $this->addTotalPrice($cartValueOption->getPrice() * -1);
                    }
                } catch (NonUniqueResultException $e) {
                    continue;
                }
            }
        }
    }

    /**
     * Get the cart value option totals for the given product.
     */
    private function getCartValueOptionTotals(CartValue $cartValue): array
    {
        $totals = [];
        $moneyFormatter = new DecimalMoneyFormatter(new ISOCurrencies());

        foreach ($cartValue->getCartValueOptions() as $cartValueOption) {
            $totals[$cartValueOption->getId()] = $moneyFormatter->format($cartValueOption->getTotal());
        }

        return $totals;
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

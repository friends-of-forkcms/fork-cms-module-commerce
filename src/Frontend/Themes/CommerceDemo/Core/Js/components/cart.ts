import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import { requestAjax } from '../api/ForkAPI';
import { lbl } from "./locale";
import { ucfirst } from "../utilities/utils";

interface CartItem {
    id: number;
    sku: string;
    name: string;
    url: string;
    thumbnail: string;
    category: string;
    brand: string;
    price: number;
    quantity: number;
    total: number;
}

interface CartData {
    totalQuantity: number;
    cartRules: {
        id: number;
        title: string;
        code: string;
        total: number;
    }[];
    subTotal: number;
    vats: {
        id: number;
        title: string;
        total: number;
    }[];
    total: number;
    items: CartItem[]
}

export default (): void => {
    window.Alpine.data('cart', () => ({
        // Cart state
        data: null as CartData | null,
        loading: false,

        // Discount form
        discountButtonLabel: ucfirst(lbl('Validate')),
        discountCode: '',
        discountFormLoading: false,
        discountValidationMessage: null as string | null,

        // Actions
        formatCurrency: (amount: number) => amount.toLocaleString(window.jsData.LANGUAGE, { style: 'currency', currency: 'EUR' }),

        /**
         * Add an item from the product page to the shopping cart. Shows a toast message.
         */
        async addItemToCart(target: HTMLButtonElement, productId: number) {
            const addToCartButton = target;

            // Prevent double submissions
            if (addToCartButton.classList.contains('is-submitting')) {
                return;
            }

            // Add a visual indicator to show the user it is submitting
            addToCartButton.classList.add('is-submitting');

            // Send the UpdateCart request
            const cartId = window.jsData?.Commerce?.cartId;
            const amount = document.querySelector<HTMLInputElement>('#product_amount')?.value || 1;
            const response = await requestAjax('Commerce', 'UpdateCart', { product: { id: productId, amount }, cartId });

            // Remove visual indicator to show the user it is submitting
            addToCartButton.classList.remove('is-submitting');

            // Dispatch an event to update the cart badge counter
            window.dispatchEvent(new CustomEvent(
                'update-cart-quantity',
                { detail: { count: response.data.cart.totalQuantity } }
            ));

            // Show notification
            const notyf = new Notyf({
                duration: 5000,
                position: { x: 'right', y: 'top' },
                dismissible: true,
            });
            const linkToCart = document.querySelector('.js-cart-nav-button')?.getAttribute('href');
            notyf.success(
                `${lbl('ProductAdded')} <br /><a href="${linkToCart}" class="underline">${lbl('ViewShoppingCart')}</a>`,
            );
        },

        /**
         * Delete an item from the shopping cart
         */
        removeItemFromCart(cartValueId: number) {
            this.loading = true;
            return requestAjax(
                'Commerce',
                'RemoveProductFromCart',
                { cart: { value_id: cartValueId } }
            ).then((response) => {
                this.data = response.data.cart
                if (this.data?.totalQuantity === 0) {
                    window.location = location;
                }
            })
        },

        /**
         * Delete a used coupon code from the shopping cart
         */
        removeDiscountCodeFromCart(cartRuleId: number) {
            this.loading = true;
            return requestAjax('Commerce', 'RemoveCartRule', { cartRuleId })
                .then((response) => this.data = response.data.cart)
        },

        /**
         * Update the quantity of a product in the shopping cart
         */
        async updateProductQuantity(cartValueId: number, quantity: number) {
            this.loading = true;
            if (quantity < 1) {
                quantity = 1;
            }

            try {
                const response = await requestAjax(
                    'Commerce',
                    'UpdateProductCart',
                    { cartValueId, amount: quantity });
                this.data = response.data.cart;

                // Dispatch an event to update the cart badge counter
                window.dispatchEvent(new CustomEvent(
                    'update-cart-quantity',
                    { detail: { count: response.data.cart.totalQuantity } }
                ));
            } catch (err) {
                console.error(err);
            } finally {
                this.loading = false;
            }
        },

        /**
         * Submit a discount code to the shopping cart
         */
        async submitDiscountCode() {
            this.discountValidationMessage = null;
            this.discountButtonLabel = 'Loading...'
            this.discountFormLoading = true;

            try {
                const response = await requestAjax(
                    'Commerce',
                    'AddCartRule',
                    { code: this.discountCode });

                if (response.code !== 200) {
                    this.discountValidationMessage = response.message;
                } else {
                    this.data = response.data.cart;
                }
            } catch(err) {
                console.error(err);
                this.discountValidationMessage = 'Error';
            } finally {
                this.discountCode = '';
                this.discountFormLoading = false;
                this.discountButtonLabel = ucfirst(lbl('Validate'));
            }
        },
    }));
};

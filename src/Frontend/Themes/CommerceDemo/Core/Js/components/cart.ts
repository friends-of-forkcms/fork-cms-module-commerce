import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import { requestAjax } from '../api/ForkAPI';
import { lbl } from './locale';

export default (): void => {
    window.Alpine.data('cart', () => ({
        items: [],
        subTotal: 0,
        vatItems: [] as {id: string, title: string, total: number}[],
        cartTotal: 0,

        formatNumber: (amount: number) => amount.toLocaleString(window.jsData.LANGUAGE, { style: 'currency', currency: 'EUR' }),
        addItemToCart: (target: HTMLButtonElement, productId: number) => addToCartHandler(target, productId),
        removeItemFromCart: (cartValueId: number) => removeItemFromCartHandler(cartValueId),
        async updateProductQuantity(cartValueId: number, quantity: number) {
            if (quantity < 1) {
                quantity = 1;
            }

            try {
                const response = await requestAjax(
                    'Commerce',
                    'UpdateProductCart',
                    { cartValueId, amount: quantity });

                this.subTotal = Number(response.data.cart.subTotal);
                this.cartTotal = Number(response.data.cart.total);
                console.log(response.data.cart.total);
                this.vatItems = Object.entries(response.data.cart.vats || {})
                    .map(([id, cart]) => ({
                        id,
                        title: (cart as any).title as string,
                        total: Number((cart as any).total) as number,
                    }));
            } catch (err) {
                alert('An error occurred');
            }
        }
    }));
};

async function addToCartHandler(target: HTMLButtonElement, productId: number) {
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

    // Update the cart badge
    const totalQuantityValue = document.querySelector('[data-cart-total-quantity]');
    if (totalQuantityValue !== null) {
        totalQuantityValue.innerHTML = response.data.cart.totalQuantity;
        totalQuantityValue.classList.remove('hidden');
    }

    // Show notification
    const notyf = new Notyf({
        duration: 5000,
        position: { x: 'right', y: 'top' },
        dismissible: true,
    });
    const linkToCart = document.querySelector('.js-cart-nav-button')?.getAttribute('href');
    notyf.success(
        `${await lbl('ProductAdded')} <br /><a href="${linkToCart}" class="underline">${await lbl(
            'ViewShoppingCart',
        )}</a>`,
    );
}

function removeItemFromCartHandler(cartValueId: number) {
    return requestAjax(
        'Commerce',
        'RemoveProductFromCart',
        { cart: { value_id: cartValueId } }
    ).then(() => window.location = location)
}

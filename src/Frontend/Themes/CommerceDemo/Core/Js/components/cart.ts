import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import { requestAjax } from '../api/ForkAPI';
import { lbl } from './locale';

export default (addToCartSelector: string): void => {
    const addToCartButton = document.querySelector<HTMLAnchorElement>(addToCartSelector);
    if (!addToCartButton || !addToCartButton.dataset.id) {
        return;
    }

    addToCartButton.addEventListener('click', addToCartHandler);
};

async function addToCartHandler(event: MouseEvent) {
    const addToCartButton = event?.currentTarget as HTMLAnchorElement;

    // Prevent double submissions
    if (addToCartButton.classList.contains('is-submitting')) {
        event.preventDefault();
    }

    // Add a visual indicator to show the user it is submitting
    addToCartButton.classList.add('is-submitting');

    // Send the UpdateCart request
    const productId = addToCartButton?.dataset.id;
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

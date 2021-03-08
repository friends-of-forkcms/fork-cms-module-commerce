import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import { requestAjax } from '../api/ForkAPI';

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

    const productId = addToCartButton?.dataset.id;
    const cartId = window.jsData?.Commerce?.cartId;
    const amount = document.querySelector<HTMLInputElement>('#product_amount')?.value || 1;

    const response = await requestAjax('Commerce', 'UpdateCart', { product: { id: productId, amount }, cartId });
    console.log(response);

    //     $('[data-cart-total-quantity]').html(response.data.cart.totalQuantity);
    //     $('[data-cart-total]').html('&euro; ' + response.data.cart.total);

    //     $('#productAddedOrderModal').modal('show');

    // Remove visual indicator to show the user it is submitting
    addToCartButton.classList.remove('is-submitting');

    if (window.lblProductAdded) {
        const notyf = new Notyf({
            duration: 5000,
            ripple: false,
            position: { x: 'right', y: 'top' },
            dismissible: true,
        });
        notyf.success(window.lblProductAdded);
    }
}

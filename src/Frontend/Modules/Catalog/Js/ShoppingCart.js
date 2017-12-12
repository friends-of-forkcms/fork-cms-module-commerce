$(function(){
    var cartWidget = $('.cartWidget');

    $('.addToCart').click(function(e){
        e.preventDefault(); // Should always use javascript:void(0); but this is a fallback

        // Skip when no product id available
        if (!$(this).data('id')) {
            return;
        }

        // Some special stuff when we are on a product detail page
        if (jsFrontend.data.get('Catalog.isProductDetail')) {
            var data = $('form.product').serialize();

            $.ajax(
                {
                    method : "POST",
                    data : data,
                    xhrFields: { withCredentials: true }
                }
            ).done(function(response){
                $('[data-cart-total-quantity]').html(response.data.cart.totalQuantity);

                $('#productAddedOrderModal').modal('show');
            }).fail(function(jqXHR) {
            });
        } else {

        }
    });

    $('.addToQuote').click(function(e){
        e.preventDefault(); // Should always use javascript:void(0); but this is a fallback

        // Skip when no product id available
        if (!$(this).data('id')) {
            return;
        }

        // Some special stuff when we are on a product detail page
        if (jsFrontend.data.get('Catalog.isProductDetail')) {
            var data = $('form.product').serialize();

            $.ajax(
                {
                    method : "POST",
                    data : data,
                    xhrFields: { withCredentials: true }
                }
            ).done(function(response){
                $('[data-cart-total-quantity]').html(response.data.cart.totalQuantity);

                $('#productAddedQuoteModal').modal('show');
            });
        } else {

        }
    });
});

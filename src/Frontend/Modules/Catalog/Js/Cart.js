$(function(){
    $('.shopping-cart [data-product]').change(function(){
        var productId = $(this).data('product'),
            product = {
                id : productId,
                amount : $(this).val(),
                overwrite : true
            };

        $('[name*="product['+productId+']"][data-option]').each(function(){
            product[$(this).data('option')] = $(this).val();
        });

        // When this is a quote request allow to overwrite
        if (jsFrontend.data.exists('Catalog.isQuote')) {
            product['quote'] = true;
        }

        $.ajax(
            {
                method : "POST",
                data : {
                    fork : {
                        module : 'Catalog',
                        action : 'UpdateCart'
                    },
                    product : product
                },
                xhrFields: { withCredentials: true }
            }
        ).done(function(response){
            $('.productsInCart').html(response.data.totalQuantity);
            $('[data-total="' + productId +'"]').html('&euro; ' + response.data.product.total);
            $('[data-sub-total]').html('&euro; ' + response.data.cart.subTotal);
            $('[data-cart-total]').html('&euro; ' + response.data.cart.total);

            // Set the options totals
            $.each(response.data.product.options, function(key, value){
                $('[data-total="' + productId +'_option_'+ key +'"]').html('&euro; ' + value);
            });

            $.each(response.data.cart.vats, function(key, vat){
                $('[data-vat="' + key +'"]').html('&euro; '+ vat['total'] );
            });
        }).fail(function(jqXHR) {
            alert(jqXHR.responseJSON.data.error);
        });
    });

    $('[data-remove-product]').click(function(e){
        e.preventDefault();

        $.ajax(
            {
                method : "POST",
                data : {
                    fork : {
                        module : 'Catalog',
                        action : 'RemoveProductFromCart'
                    },
                    product : {
                        id : $(this).data('removeProduct')
                    }
                },
                xhrFields: { withCredentials: true }
            }
        ).done(function(response){
            window.location = window.location;
        });
    });
});

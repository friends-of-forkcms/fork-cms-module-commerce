$(function () {
  $('.shopping-cart [data-product]').change(function () {
    var productId = $(this).data('product'),
      data = {
        fork: {
          module: 'Catalog',
          action: 'UpdateProductCart'
        },
        id: productId,
        cartValueId: null,
        amount: parseInt($(this).val(), 10),
        overwrite: true
      };

    if (data.amount < 1) {
      data.amount = 1;
      $(this).val(1);
    }

    if ($(this).data('cartValueId')) {
      data.cartValueId = $(this).data('cartValueId');
    }

    $('[name*="product[' + productId + ']"][data-option]').each(function () {
      data[$(this).data('option')] = $(this).val();
    });

    // When this is a quote request allow to overwrite
    if (jsFrontend.data.exists('Catalog.isQuote')) {
      data['quote'] = true;
    }

    $.ajax(
      {
        method: "POST",
        data,
        xhrFields: {withCredentials: true}
      }
    ).done(function (response) {
      $('.productsInCart').html(response.data.totalQuantity);
      $('[data-sub-total]').html('&euro; ' + response.data.cart.subTotal);
      $('[data-cart-total]').html('&euro; ' + response.data.cart.total);


      if (data.cartValueId) {
        $('[data-cart-value-id="' + data.cartValueId + '"] [data-total="' + productId + '"]').html('&euro; ' + response.data.product.total);
        $.each(response.data.product.options, function (key, value) {
          $('[data-cart-value-id="' + data.cartValueId + '"] [data-total="' + productId + '_option_' + key + '"]').html('&euro; ' + value);
        });
      } else {
        $('[data-total="' + productId + '"]').html('&euro; ' + response.data.product.total);
        // Set the options totals
        $.each(response.data.product.options, function (key, value) {
          $('[data-total="' + productId + '_option_' + key + '"]').html('&euro; ' + value);
        });
      }

      $.each(response.data.cart.vats, function (key, vat) {
        $('[data-vat="' + key + '"]').html('&euro; ' + vat['total']);
      });
    }).fail(function (jqXHR) {
      var error = 'Er is een fout opgetreden:';

      $.each(jqXHR.responseJSON.data.errors.fields, function (key, value) {
        error += '\n\t' + value;
      });

      alert(error);
    });
  });

  $('[data-remove-product]').click(function (e) {
    e.preventDefault();

    $.ajax(
      {
        method: "POST",
        data: {
          fork: {
            module: 'Catalog',
            action: 'RemoveProductFromCart'
          },
          cart: {
            value_id: $(this).data('removeProduct')
          }
        },
        xhrFields: {withCredentials: true}
      }
    ).done(function (response) {
      $(document).trigger({
        type: 'catalog.product.remove_from_cart',
        'product': response.data.product
      });

      window.location = window.location;
    });
  });
});

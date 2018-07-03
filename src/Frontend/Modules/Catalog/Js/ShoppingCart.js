$(function () {
  $('.addToCart').click(function (e) {
    e.preventDefault(); // Should always use javascript:void(0); but this is a fallback
    e.stopPropagation();

    // Skip when no product id available
    if (!$(this).data('id')) {
      return;
    }

    // Some special stuff when we are on a product detail page
    if (jsFrontend.data.exists('Catalog') && jsFrontend.data.get('Catalog')['isProductDetail']) {
      var data = $('form[name=product]').serialize();

      // Cleanup errors
      var errorFields = $('.bg-white.error');
      errorFields.find('.error').remove();
      errorFields.removeClass('error');

      $.ajax(
        {
          method: "POST",
          data: data
        }
      ).done(function (response) {
        $(document).trigger({
          type: 'catalog.product.add_to_cart',
          'product': response.data.product
        });

        $('[data-cart-total-quantity]').html(response.data.cart.totalQuantity);

        $('#productAddedOrderModal').modal('show');
      }).fail(function (response) {
        $.each(response.responseJSON.data.errors.fields, function (key, value) {
          var field = $('[name*="\[' + key + '\]"'),
            parent = field.closest('.bg-white');

          parent.addClass('error');
          parent.append($('<span>').html(value).addClass('error'));
        });
      });
    } else {
      $.ajax(
        {
          method: "POST",
          data: {
            fork: {
              module: 'Catalog',
              action: 'UpdateCart'
            },
            product: {
              id: $(this).data('id'),
              amount: $(this).data('orderQuantity')
            }
          }
        }
      ).done(function (response) {
        $(document).trigger({
          type: 'catalog.product.add_to_cart',
          'product': response.data.product
        });

        $('[data-cart-total-quantity]').html(response.data.cart.totalQuantity);

        $('#productAddedOrderModal').modal('show');
      }).fail(function (response) {
        window.location = response.responseJSON.data.errors.url;
      });
    }
  });

  $('.addToQuote').click(function (e) {
    e.preventDefault(); // Should always use javascript:void(0); but this is a fallback

    // Skip when no product id available
    if (!$(this).data('id')) {
      return;
    }

    // Some special stuff when we are on a product detail page
    if (jsFrontend.data.get('Catalog.isProductDetail')) {
      var data = $('form[name=product]').serialize();

      $.ajax(
        {
          method: "POST",
          data: data,
          xhrFields: {withCredentials: true}
        }
      ).done(function (response) {
        $('[data-cart-total-quantity]').html(response.data.cart.totalQuantity);

        $('#productAddedQuoteModal').modal('show');
      });
    } else {

    }
  });
});

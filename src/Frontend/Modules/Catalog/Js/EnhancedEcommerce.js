$(function () {
  if (typeof ga === 'undefined') {
    return;
  }

  ga('require', 'ec');

  runEnhancedEcommerce();

  $(document).on('catalog.products.filtered', function () {
    runEnhancedEcommerce();
  });


  // Handle the click on a product item
  $(document).on('click', '.product-item', function (e) {
    e.preventDefault();
    e.stopImmediatePropagation();

    var name = $(this).closest('[data-product-list]').data('productList'),
      product = $(this);

    ga('ec:addProduct', getProductData(product, name, product.index()));
    ga('ec:setAction', 'click', {list: name});
    ga('send', 'event', 'UX', 'click', 'Results', {
      hitCallback: function () {
        document.location = product.data('url');
      }
    });
  });

  // Handle product added to cart
  $(document).on('catalog.product.add_to_cart', function (e) {
    ga('ec:addProduct', {
      'id': e.product.sku,
      'name': e.product.title,
      'category': e.product.category,
      'brand': e.product.brand,
      'price': e.product.total,
      'quantity': e.product.quantity
    });
    ga('ec:setAction', 'add');
    ga('send', 'event', 'UX', 'click', 'add to cart');
  });

  // Handle product added to cart
  $(document).on('catalog.product.remove_from_cart', function (e) {
    ga('ec:addProduct', {
      'id': e.product.sku,
      'name': e.product.title,
      'category': e.product.category,
      'brand': e.product.brand,
      'price': e.product.total,
      'quantity': e.product.quantity
    });
    ga('ec:setAction', 'remove');
    ga('send', 'event', 'UX', 'click', 'remove from cart');
  });

  // Product detail view
  $('[data-product-detail]').each(function () {
    ga('ec:addProduct', getProductData($(this)));
    ga('ec:setAction', 'detail');
  });

  function runEnhancedEcommerce() {
    $('[data-product-list]').each(function () {
      var name = $(this).data('productList');

      $('.product-item').each(function () {
        ga('ec:addImpression', getProductData($(this), name, $(this).index()));
      });
    });
  }

  function getProductData(productObject, listName, index) {
    var data = {
      'id': productObject.data('sku'),
      'name': productObject.data('name'),
      'category': productObject.data('category'),
      'brand': productObject.data('brand')
    };

    if (typeof listName !== 'undefined') {
      data['list'] = listName;
    }

    if (typeof index !== 'undefined') {
      data['index'] = index;
    }

    return data;
  }
});

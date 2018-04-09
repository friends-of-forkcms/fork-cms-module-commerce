$(function(){
  var productText = $('#productText'),
      readMoreHeight = 400;

  if (productText.outerHeight() >= readMoreHeight) {
    $('<p>')
      .attr('id', 'readMoreHolder')
      .append(
        $('<a>')
          .addClass('product-text-read-more')
          .attr('href', 'javascript:void(0);')
          .html(jsFrontend.locale.lbl('ShowMore'))
      )
      .insertAfter(productText);
  }

  $(document).on('click', '.product-text-read-more', function(e){
    e.preventDefault();

    $('#readMoreHolder').remove();

    productText.css({
      'max-height' : 'initial',
      'overflow' : 'auto'
    });
  });

  $('.product-slider-thumb .thumbnails').owlCarousel({
    stagePadding: 0,
    margin:5,
    nav:true,
    dots: false,
    items: 4,
    loop:true
  });
});

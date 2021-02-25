/**
 * Interaction for the Commerce module
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
jsFrontend.commerce =
    {
        // constructor
        init: function () {
            jsFrontend.commerce.productDetail();
        },

        productDetail: function () {
            var photoHolder = $(".product-photo-slider"),
                thumbnails = $('.thumb-slider .thumbnails');

            // Stop executing when there are no photos to display
            if (photoHolder.length === 0) {
                return;
            }

            var slider = photoHolder.sudoSlider(
                {
                    effect: 'slide',
                    prevNext: true,
                    responsive: true,
                    speed: 300,
                    auto: false,
                    pause: '5000',
                    beforeAnimation: function (i, j) {
                        $('.thumb-slider .thumbnails').find('.current').removeClass('current');
                        thumbnails.find('a[data-index='+ i +']').closest('li').addClass('current');
                    }
                }
            );

            thumbnails.on('click', 'a[data-index]', function (e) {
                e.preventDefault();

                thumbnails.find('.current').removeClass('current');
                $(this).parent().addClass('current');

                slider.goToSlide($(this).data('index'));
            });

            $('[data-fancybox]').fancybox({
                thumbs : {
                    autoStart : true
                }
            });

            $('.parts-small').owlCarousel({
                stagePadding: 0,
                margin:15,
                nav:true,
                responsive:{
                    0:{
                        items:1
                    },
                    600:{
                        items:2
                    },
                    800: {
                        items:2
                    },
                    1100:{
                        items:3
                    },
                    1500:{
                        items:3
                    },
                    1600:{
                        items:3
                    }
                }
            });
        }
    };

$(jsFrontend.commerce.init);

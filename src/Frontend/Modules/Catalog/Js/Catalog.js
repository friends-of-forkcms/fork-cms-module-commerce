/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Interaction for the Catalog module
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
jsFrontend.catalog =
    {
        // constructor
        init: function () {
            jsFrontend.catalog.productDetail();
        },

        productDetail: function () {
            var photoHolder = $(".product-photo-slider"),
                thumbnails = $('.thumb-slider .thumbnails li');

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
                        thumbnails.filter('.current').removeClass('current');
                        thumbnails.eq(i - 1).addClass('current');
                    }
                }
            );

            thumbnails.find('a').click(function (e) {
                e.preventDefault();

                thumbnails.find('> .current').removeClass('current');
                slider.goToSlide($(this).parent().addClass('current').index() + 1);
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

$(jsFrontend.catalog.init);

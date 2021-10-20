import { Splide } from '@splidejs/splide';
import { Video } from '@splidejs/splide-extension-video';

import '@splidejs/splide/dist/css/splide.min.css';
import '@splidejs/splide-extension-video/dist/css/splide-extension-video.min.css';

/**
 * Create a carousel, e.g. for viewing the product images.
 * We use Splide, a lightweight, powerful and flexible slider. It's written in pure JS without any dependencies!
 * @see https://splidejs.com/thumbnail-slider/
 */
export default (carouselSelector: string): void => {
    const thumbnailSlider = new Splide(`${carouselSelector}-thumb`, {
        fixedWidth: 100,
        speed: 250,
        height: 60,
        gap: 10,
        cover: true,
        isNavigation: true,
        pagination: false,
        focus: 'center',
        arrows: false,
        waitForTransition: false,
        keyboard: false, // prevent double pages
        breakpoints: {
            '600': {
                fixedWidth: 66,
                height: 40,
            },
        },
    }).mount();

    const primarySlider = new Splide(carouselSelector, {
        type: 'fade',
        heightRatio: 0.5,
        pagination: false,
        speed: 250,
        arrows: true,
        cover: true,
        waitForTransition: false,
    });

    // Connect the primary slider to the second one, and mount the video plugin.
    primarySlider.sync(thumbnailSlider).mount({ Video });
};

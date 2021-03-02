import Splide from '@splidejs/splide';
import '@splidejs/splide/dist/css/splide.min.css';

/**
 * Create a carousel, e.g. for viewing the product images.
 * We use Splide, a lightweight, powerful and flexible slider. It's written in pure JS without any dependencies!
 * @see https://splidejs.com/thumbnail-slider/
 */
export default (carouselSelector: string): void => {
    const thumbnailSlider = new Splide(`${carouselSelector}-thumb`, {
        fixedWidth: 100,
        height: 60,
        gap: 10,
        cover: true,
        isNavigation: true,
        focus: 'center',
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
        arrows: false,
        cover: true,
    });

    // Connect the primary slider to the second one.
    primarySlider.sync(thumbnailSlider).mount();
};

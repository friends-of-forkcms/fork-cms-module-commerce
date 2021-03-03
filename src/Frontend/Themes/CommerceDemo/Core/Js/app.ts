// Styles
import '../Layout/Css/app.css';

// Components

document.addEventListener('DOMContentLoaded', () => {
    // Dynamic imports with code splitting for lazy loading
    // Lazy, or "on demand", loading is a great way to optimize your site or application. This practice essentially involves splitting your
    // code at logical breakpoints, and then loading it once the user has done something that requires, or will require, a new block of code.
    // This speeds up the initial load of the application and lightens its overall weight as some blocks may never even be loaded.
    const carouselSelector = '.js-product-slider';
    if (document.querySelector(carouselSelector)) {
        import('./components/carousel').then((carousel) => carousel.default(carouselSelector));
        import('./components/photoswipe').then((photoswipe) => photoswipe.default('.photoswipe-inner'));
    }
});

// Check if HMR is enabled, then accept itself.
if (module.hot) {
    module.hot.accept();
}

// Styles
import '../Layout/Css/app.css';

// Enable AlpineJS, a minimal framework for adding "just enough" JS behavior to our HTML.
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse'
import persist from '@alpinejs/persist'
Alpine.plugin(collapse)
Alpine.plugin(persist)
window.Alpine = Alpine;

// Components
import filters from './components/filters';
import cart from './components/cart';
import { search } from './components/search';
import { init as initLocale } from "./components/locale";

document.addEventListener('DOMContentLoaded', async () => {
    // Load the Fork CMS locale before other components load
    await initLocale();
    filters();
    cart();
    search();

    // Dynamic imports with code splitting for lazy loading
    // Lazy, or "on demand", loading is a great way to optimize your site or application. This practice essentially involves splitting your
    // code at logical breakpoints, and then loading it once the user has done something that requires, or will require, a new block of code.
    // This speeds up the initial load of the application and lightens its overall weight as some blocks may never even be loaded.
    const carouselSelector = '.js-product-slider';
    if (document.querySelector(carouselSelector)) {
        import('./components/carousel').then((carousel) => carousel.default(carouselSelector));
        import('./components/photoswipe').then((photoswipe) => photoswipe.default('.photoswipe-inner'));
    }

    // Start Alpine after the components have loaded
    // @see https://alpinejs.dev/globals/alpine-data#registering-from-a-bundle
    Alpine.start();
});

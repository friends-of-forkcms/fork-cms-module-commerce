// Styles
import '../Layout/Css/app.css';

// Components
// Import javascript components here

// Check if HMR is enabled, then accept itself.
if (module.hot) {
    module.hot.accept();

    // Make sure we trigger turbolinks to do a page load and re-init the components to see our changes.
    module.hot.addStatusHandler((status) => {
        if (status === 'idle') {
            Turbolinks.dispatch('turbolinks:load');
        }
    });
}

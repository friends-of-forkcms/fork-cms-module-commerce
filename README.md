# Fork CMS Catalog module
This catalog module is insipred by the created by Webleads. We upgraded this module from Fork version 3 to version 5. By this we are able to use more of the Fork features.

## Capabilities
At the moment it is almost the same as the Webleads module, but we left out some code and are still working on this, our changes are:
* Improved the products by adding:
  * Related products
  * Special prices (sale prices)
  * Added a article number
  * Added VAT options
  * Add product specifications on the flow. The user gets the option to create or select one while adding or editing a product

We also removed some components:
* The media manager has been removed in favour of the default Fork media manager
* Removed comments on products (may return in the future)

## How to install

### 1. Upload the module
Upload this module as usual, copy the `Catalog` folder from the `Backend` and `Frontend`.

### 2. Run composer
This module requires extra depencies, you can install these by running:

```
composer require tetranz/select2entity-bundle
```

### 3. Install extra JavaScript and CSS

Run:
```
yarn add select2
```

Add some lines to `gulpfile.js`:
* Search for `gulp.task("build:backend:assets:copy-css-vendors", function() {` and add `"node_modules/select2/dist/css/select2.min.css",` to the `.src` array.
* Search for `gulp.task("build:backend:assets:copy-js-vendors", function() {` and add `
    "node_modules/select2/dist/js/i18n/nl.js",
    "node_modules/select2/dist/js/i18n/en.js",
    "node_modules/select2/dist/js/select2.full.min.js",` to the `.src` array.

And than build the files:
```
gulp build
```

Now you should be able to run this module.

At the moment we are working on the backend, frontend is slowly to be finished.

# Fork CMS Catalog module

This catalog module is insipred by the created by Webleads. We upgraded this module from Fork version 3 to version 5. By this we are able to use more of the Fork features.

## Capabilities

At the moment it is almost the same as the Webleads module, but we left out some code and are still working on this, our changes are:

- Improved the products by adding:
  - Related products
  - Special prices (sale prices)
  - Added a article number
  - Added VAT options
  - Add product specifications on the flow. The user gets the option to create or select one while adding or editing a product

We also removed some components:

- The media manager has been removed in favour of the default Fork media manager
- Removed comments on products (may return in the future)

## How to install

### 1. Upload the module

Upload this module as usual, copy the `Catalog` folder from the `Backend` and `Frontend`.

### 2. Run composer

This module requires extra dependencies, you can install these by running:

```
composer require tetranz/select2entity-bundle
composer require knplabs/knp-snappy-bundle
composer require h4cc/wkhtmltopdf-amd64
```

Enable KnpSnappyBundle and new TetranzSelect2EntityBundle in your kernel:

```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = [
        //...
        new \Tetranz\Select2EntityBundle\TetranzSelect2EntityBundle(),
        new \Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
        //...
    ];
}
```

Add the wkhtmltopdf path to your config file:

```yaml
# app/config/config.yml
knp_snappy:
  pdf:
    enabled: true
    binary: %wkhtmltopdf.binary%
```

And update your parameters.yml with the following:

```yaml
wkhtmltopdf.binary: %kernel.root_dir%/../vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64
```

You may edit this value match your needs.

### 3. Install extra JavaScript and CSS

Run:

```npm
yarn add select2
```

Add some lines to `gulpfile.js`:

- Search for `gulp.task("build:backend:assets:copy-css-vendors", function() {` and add `"node_modules/select2/dist/css/select2.min.css",` to the `.src` array.
- Search for `gulp.task("build:backend:assets:copy-js-vendors", function() {` and add ` "node_modules/select2/dist/js/i18n/nl.js", "node_modules/select2/dist/js/i18n/en.js", "node_modules/select2/dist/js/select2.full.min.js",` to the `.src` array.

And than build the files:

```
gulp build
```

Now you should be able to run this module.

At the moment we are working on the backend, frontend is slowly to be finished.

### 4. Configure LiipImagineBundle filters

````
Configure the following filters to your config file:

```yaml
# app/config/config.yml
liip_imagine:
    filter_sets:
        ...
        product_thumbnail:
            filters:
                auto_rotate: ~
                strip: ~
                scale: { dim: [ 300, 380 ] }
        product_large:
            filters:
                auto_rotate: ~
                strip: ~
                scale: { dim: [ 600, 800 ] }
        product_slider_thumbnail:
            filters:
                auto_rotate: ~
                strip: ~
                scale: { dim: [ 100, 100 ] }
````

## Start selling

After setting up Mollie or Buckaroo you are able to sell your products!

But to increase sales and SEO we added some tools.

### 1. Sitemap generator

Install the sitemap generator build bij jeroondesloovere: https://github.com/friends-of-forkcms/fork-cms-module-sitemaps

When you create a Cronjob you should be able to generate a sitemap each day.

### 2. Google Shopping Feed

When you assign Google Shopping Categories to your categories you would be able to display products in Google Shopping.

Setup is really easy:

1. Assign the right categories
2. Create a cronjob which generates the feed daily, the cronjob command is: `php bin/console catalog:generate-merchant-feed`
3. Add the feed to your Google Mechant Center
4. Start selling products

This feeds also works for Bing!

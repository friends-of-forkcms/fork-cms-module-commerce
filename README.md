# Fork CMS Commerce (WIP)

This commerce module is inspired by the catalog module created by Webleads. [Jacob Van Dam](https://www.jvdict.nl) upgraded and improved this module massively.

## Demo

- Frontend: https://fork-cms-module-commerce-demo-jessedobbelaere.cloud.okteto.net/
- CMS: https://fork-cms-module-commerce-demo-jessedobbelaere.cloud.okteto.net/private

Login with the following credentials:

```
Admin username: demo@fork-cms.com
Admin password: demo
```

## Capabilities

- Related products
- Special prices (sale prices)
- Added a article number
- Added VAT options
- Add product specifications on the flow. The user gets the option to create or select one while adding or editing a product
- Demo frontend theme `CommerceDemo` for the [demo website](#demo), built using an opiniated, modern stack of [Vite.js](https://vitejs.dev), [Tailwind CSS](https://tailwindcss.com/), with a sprinkle of [AlpineJS](https://alpinejs.dev/) and Typescript.
- All money amounts in the module are represented internally as "cents" - integers [Martin Fowler's Money pattern](http://martinfowler.com/eaaCatalog/money.html)

## How to install

### 1. Upload the module

Install this module as usual, by copying the `Commerce` folder from the `Backend` and `Frontend` folders to your project.

### 2. Install dependencies

This module requires extra Composer dependencies, you can install these by running:

```bash
composer require tetranz/select2entity-bundle "v2.10.1"
composer require knplabs/knp-snappy-bundle "v1.6.0"
composer require h4cc/wkhtmltopdf-amd64 "^0.12.4"
composer require gedmo/doctrine-extensions "^3.0"
composer require jeroendesloovere/sitemap-bundle "^2.0"
composer require moneyphp/money "v3.3.1"

# In case you want to load the demo fixtures or run tests
composer require --dev doctrine/doctrine-fixtures-bundle
composer require --dev zenstruck/foundry
```

Enable bundles in your kernel:

```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = [
        //...
        new \Tetranz\Select2EntityBundle\TetranzSelect2EntityBundle(),
        new \Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
        new \Backend\Modules\Sitemaps\Sitemaps(),
        new \JeroenDesloovere\SitemapBundle\SitemapBundle(),
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

### 3. Configure LiipImagineBundle filters

Configure the following filters to your config file, to use properly resized images in the frontend.

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
```

### 4. Add a Twig extension to parse script/link tags

The frontend theme builds assets to a dist/ folder. To include these assets, Twig can use a manifest.json file. The included `ViteAssetExtension.php` should help do that.
However, a theme cannot register Symfony services (yet), so we have to add it manually to `config.yml`.

```yaml
services:
    ...

    # Configure the twig extension for ViteJS to easily switch between dev and prod script tags
    Frontend\Themes\CommerceDemo\ViteAssetExtension:
        autowire: true
        arguments:
            $basePublicPath: '/src/Frontend/Themes/CommerceDemo/dist/'
            $manifest: '%kernel.project_dir%/src/Frontend/Themes/CommerceDemo/dist/manifest.json'
            $devServerPublic: 'http://localhost:3000/src/Frontend/Themes/CommerceDemo/'
            $environment: '%kernel.environment%'
        tags:
            - { name: twig.extension }
```

Now simply build the frontend:

```bash
cd src/Frontend/Themes/CommerceDemo
npm run build # or npm run dev
```

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

## Contributors ✨

Thanks goes to these wonderful people ([emoji key](https://allcontributors.org/docs/en/emoji-key)):

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tr>
    <td align="center"><a href="https://github.com/jacob-v-dam"><img src="https://avatars.githubusercontent.com/u/310526?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Jacob van Dam</b></sub></a><br /><a href="https://github.com/friends-of-forkcms/fork-cms-module-commerce/commits?author=jacob-v-dam" title="Code">💻</a></td>
    <td align="center"><a href="https://github.com/wolfie90"><img src="https://media-exp1.licdn.com/dms/image/C4E03AQEbw6iOOzJySA/profile-displayphoto-shrink_800_800/0/1539623620018?e=1620259200&v=beta&t=Z8Rcs1enc9IfbeUskq8olG6g6GsknlzlndKhzK9Vo0g" width="100px;" alt=""/><br /><sub><b>wolfie</b></sub></a><br /><a href="https://github.com/friends-of-forkcms/fork-cms-module-commerce/commits?author=wolfie90" title="Code">💻</a></td>
    <td align="center"><a href="https://jessedobbelae.re/"><img src="https://avatars.githubusercontent.com/u/1352979?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Jesse Dobbelaere</b></sub></a><br /><a href="https://github.com/friends-of-forkcms/fork-cms-module-commerce/commits?author=jessedobbelaere" title="Code">💻</a></td>
  </tr>
</table>

<!-- markdownlint-restore -->
<!-- prettier-ignore-end -->

<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [all-contributors](https://github.com/all-contributors/all-contributors) specification. Contributions of any kind welcome!

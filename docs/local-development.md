# Local development

## Symlinking the module folders

When developing locally, I usually create a symlinks between the folders in this git repository (`fork-cms-module-commerce`) 
and an actual Fork CMS installation (`fork-cms-module-commerce-demo`) to develop against in PhpStorm, e.g.:

```bash
ln -sf ~/Code/fork-cms/modules/fork-cms-module-commerce/src/Backend/Modules/Commerce ~/Code/fork-cms/modules/fork-cms-module-commerce-demo/src/Backend/Modules/Commerce
ln -sf ~/Code/fork-cms/modules/fork-cms-module-commerce/src/Backend/Modules/CommerceCashOnDelivery ~/Code/fork-cms/modules/fork-cms-module-commerce-demo/src/Backend/Modules/CommerceCashOnDelivery
ln -sf ~/Code/fork-cms/modules/fork-cms-module-commerce/src/Backend/Modules/CommercePickup ~/Code/fork-cms/modules/fork-cms-module-commerce-demo/src/Backend/Modules/CommercePickup
ln -sf ~/Code/fork-cms/modules/fork-cms-module-commerce/src/Frontend/Modules/Commerce ~/Code/fork-cms/modules/fork-cms-module-commerce-demo/src/Frontend/Modules/Commerce
ln -sf ~/Code/fork-cms/modules/fork-cms-module-commerce/src/Frontend/Themes/CommerceDemo ~/Code/fork-cms/modules/fork-cms-module-commerce-demo/src/Frontend/Themes/CommerceDemo
```

This is optional of course, but it's far easier than having to copy the module code from/to your Fork CMS installation each time you're ready to commit.

⚠️ One downside is that PhpStorm's `Run with Coverage` button will not work properly. The test coverage will be calculated on the original files,
but PhpStorm only knows the symlinked folders. One way to overcome this is written below:

<details>
<summary>Synchronize folders between git repo and fork cms demo locally</summary>

```bash
# Install macFuse and bindfs
brew install --cask macfuse
brew install gromgit/fuse/bindfs-mac

# Create the empty module folder structure in a new Fork CMS project 
cd fork-cms-module-commerce-demo
mkdir -p src/Backend/Modules/{Commerce,CommerceCashOnDelivery,CommercePickup} src/Frontend/Modules/Commerce src/Frontend/Themes/CommerceDemo

# Bind mount the folders from the source (git repo) to our Fork CMS demo project.
bindfs -o volname=Commerce ../fork-cms-module-commerce/src/Backend/Modules/Commerce src/Backend/Modules/Commerce
bindfs -o volname=CommerceCashOnDelivery ../fork-cms-module-commerce/src/Backend/Modules/CommerceCashOnDelivery src/Backend/Modules/CommerceCashOnDelivery
bindfs -o volname=CommercePickup ../fork-cms-module-commerce/src/Backend/Modules/CommercePickup src/Backend/Modules/CommercePickup
bindfs -o volname=Commerce ../fork-cms-module-commerce/src/Frontend/Modules/Commerce src/Frontend/Modules/Commerce
bindfs -o volname=CommerceDemo ../fork-cms-module-commerce/src/Frontend/Themes/CommerceDemo src/Frontend/Themes/CommerceDemo
```

Feel welcome to suggest a better solution.

</details>

## Setup Fork CMS

See [installation](installation.md) on how to install and configure the module in your new Fork CMS installation.

## Unit testing

### Fixtures

We use [DoctrineFixturesBundle](https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html) to reset and reload fixtures data in our local DB. Execute this command with the `--append` flag to prevent erasing the whole database:

```bash
bin/console doctrine:fixtures:load --append --group=module-commerce
```

### PHPUnit

We use PHPUnit and create fixture objects using the awesome [zenstruck/foundry](https://github.com/zenstruck/foundry) library. This allows for an readable and expressive, on-demand fixture system to quickly create a certain situation, e.g. `ProductTest.php`. Check out the [Symfonycasts series on Foundry](https://symfonycasts.com/screencast/symfony-doctrine/foundry).

```php
public function it_can_get_a_discounted_price_with_vat(): void
{
    $product = ProductFactory::new()
        ->withPrice('299,99')
        ->withVat(21.00)
        ->withNewSpecial('240,00', (new DateTime())->modify('-1 day'))
        ->create();
    self::assertEquals('36299', $product->getOldPrice(true)->getAmount());
    self::assertEquals('29040', $product->getActivePrice(true)->getAmount());
    self::assertTrue($product->hasActiveSpecialPrice());
}
```

To run the all module(s) tests, simply run `simple-phpunit` using the filter option to run both backend and frontend tests:

```bash
bin/simple-phpunit --filter '\\Modules\\Commerce'
```

or run them from PhpStorm: 

1. Go to 'Add Configuration' > 'New configuration' > 'PHPUnit'
2. Enter a name and select 'Test scope: Defined in the configuration file'
3. Enter a filter in the Test runner options: `--filter '\\Modules\\Commerce'` to run both frontend and backend tests.
4. Run the tests from PhpStorm using the green play button

![PhpStorm configured to run fork-cms-module-commerce tests](img/PhpStorm-phpunit-config.png)

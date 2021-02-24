<?php

namespace Backend\Modules\Commerce\DataFixtures;

use Backend\Modules\Commerce\Domain\Brand\Command\CreateBrand;
use Backend\Modules\Commerce\Domain\Brand\Image as BrandImage;
use Backend\Modules\Commerce\Domain\Category\Command\CreateCategory;
use Backend\Modules\Commerce\Domain\Category\Image as CategoryImage;
use Backend\Modules\Commerce\Domain\Country\Command\CreateCountry;
use Backend\Modules\Commerce\Domain\OrderStatus\Command\CreateOrderStatus;
use Backend\Modules\Commerce\Domain\Product\Command\CreateProduct;
use Backend\Modules\Commerce\Domain\Product\Command\UpdateProduct;
use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\ProductOption\Command\CreateProductOption;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Backend\Modules\Commerce\Domain\ProductOptionValue\Command\CreateProductOptionValue;
use Backend\Modules\Commerce\Domain\ProductSpecial\ProductSpecial;
use Backend\Modules\Commerce\Domain\Specification\Command\CreateSpecification;
use Backend\Modules\Commerce\Domain\SpecificationValue\Command\CreateSpecificationValue;
use Backend\Modules\Commerce\Domain\StockStatus\Command\CreateStockStatus;
use Backend\Modules\Commerce\Domain\Vat\Command\CreateVat;
use Backend\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\Command\SaveMediaGroup;
use Backend\Modules\MediaLibrary\Domain\MediaItem\Command\CreateMediaItemFromLocalStorageType;
use Common\Doctrine\Entity\Meta;
use Common\Doctrine\ValueObject\SEOFollow;
use Common\Doctrine\ValueObject\SEOIndex;
use Common\Uri;
use DateTime;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Load data to populate a demo store
 * E.g. bin/console doctrine:fixtures:load --append --group=module-commerce.
 */
class DemoStoreFixtures extends BaseFixture implements FixtureGroupInterface
{
    protected array $tableNames = [
        'commerce_brands',
        'commerce_categories',
        'commerce_countries',
        'commerce_vats',
        'commerce_stock_statuses',
        'commerce_order_statuses',
        'commerce_products',
        'commerce_product_option_values',
        'commerce_product_options',
        'commerce_product_specials',
        'commerce_products_specification_values',
        'commerce_specifications',
        'commerce_specification_values',
        'MediaGroup',
        'MediaGroupMediaItem',
        'MediaItem',
    ];
    protected array $uploadFolders = [
        PATH_WWW.'/src/Frontend/Files/Commerce/brands',
        PATH_WWW.'/src/Frontend/Files/Commerce/categories',
        PATH_WWW.'/src/Frontend/Files/Cache',
        PATH_WWW.'/src/Frontend/Files/MediaLibrary',
    ];

    public static function getGroups(): array
    {
        return ['module-commerce'];
    }

    public function load(ObjectManager $manager): void
    {
        $this->cleanup($manager);
        $this->createCategories();
        $this->createBrands();
        $this->createVats();
        $this->createStockStatuses();
        $this->createOrderStatuses();
        $this->createPaymentMethods();
        $this->createCountries();
        $this->createSpecifications(); // @todo
        $this->createProducts($manager->find(MediaFolder::class, 1));
    }

    private function createCategories(): void
    {
        $categories = $this->readCsv(__DIR__.'/data/categories.csv');
        foreach ($categories as ['title' => $title, 'image' => $imageSrc]) {
            $createCategory = new CreateCategory();
            $createCategory->title = $title;
            $createCategory->meta = new Meta($title, false, $title, false, $title, false, Uri::getUrl($title), false, null, SEOFollow::none(), SEOIndex::none());
            $createCategory->image = CategoryImage::fromUploadedFile($this->fakeUploadImage(__DIR__."/assets/$imageSrc"));
            $this->commandBus->handle($createCategory);

            // Save reference for other fixtures
            $referenceKey = 'category_'.strtolower($createCategory->title);
            $this->addReference(md5($referenceKey), $createCategory->getCategoryEntity());
        }
    }

    private function createBrands(): void
    {
        $brands = $this->readCsv(__DIR__.'/data/brands.csv');
        foreach ($brands as ['title' => $title, 'image' => $imageSrc]) {
            $createBrand = new CreateBrand();
            $createBrand->title = $title;
            $createBrand->meta = new Meta($title, false, $title, false, $title, false, Uri::getUrl($title), false, null, SEOFollow::none(), SEOIndex::none());
            $createBrand->image = BrandImage::fromUploadedFile($this->fakeUploadImage(__DIR__."/assets/$imageSrc"));
            $this->commandBus->handle($createBrand);

            // Save reference for other fixtures
            $referenceKey = 'brand_'.strtolower($createBrand->title);
            $this->addReference(md5($referenceKey), $createBrand->getBrandEntity());
        }
    }

    private function createVats(): void
    {
        $vats = $this->readCsv(__DIR__.'/data/vats.csv');
        foreach ($vats as ['title' => $title, 'percentage' => $percentage]) {
            $createVat = new CreateVat();
            $createVat->title = $title;
            $createVat->percentage = $percentage;
            $this->commandBus->handle($createVat);

            // Save reference for other fixtures
            $referenceKey = 'vat_'.strtolower($createVat->title);
            $this->addReference(md5($referenceKey), $createVat->getVatEntity());
        }
    }

    private function createStockStatuses(): void
    {
        $createStockStatus = new CreateStockStatus();
        $createStockStatus->title = 'Available';
        $this->commandBus->handle($createStockStatus);

        // Save reference for other fixtures
        $referenceKey = 'stock_status_'.strtolower($createStockStatus->title);
        $this->addReference(md5($referenceKey), $createStockStatus->getStockStatusEntity());
    }

    private function createOrderStatuses(): void
    {
        $orderStatuses = ['processing', 'picked', 'shipped', 'delivered', 'cancelled', 'expired', 'refunded'];
        foreach ($orderStatuses as $status) {
            $createOrderStatus = new CreateOrderStatus();
            $createOrderStatus->title = ucfirst($status);
            $this->commandBus->handle($createOrderStatus);

            // Save reference for other fixtures
            $referenceKey = 'order_status_'.strtolower($createOrderStatus->title);
            $this->addReference(md5($referenceKey), $createOrderStatus->getOrderStatusEntity());
        }
    }

    private function createPaymentMethods(): void
    {
        // $paymentMethod = new PaymentMethod("Mollie", Locale::workingLocale());
    }

    private function createCountries(): void
    {
        $countries = ['BE' => 'Belgium'];
        foreach ($countries as $isoCode => $countryName) {
            $createCountry = new CreateCountry();
            $createCountry->name = $countryName;
            $createCountry->iso = $isoCode;
            $this->commandBus->handle($createCountry);
        }
    }

    private function createSpecifications(): void
    {
        $specifications = ['Color' => ['blue', 'orange', 'green', 'white', 'gray', 'brown', 'black', 'beige']];

        foreach ($specifications as $title => $values) {
            $createSpecification = new CreateSpecification();
            $createSpecification->title = $title;
            $createSpecification->filter = true;
            $createSpecification->meta = new Meta($title, false, $title, false, $title, false, Uri::getUrl($title), false, null, SEOFollow::none(), SEOIndex::none());
            $this->commandBus->handle($createSpecification);

            $sequence = 1;
            foreach ($values as $value) {
                $createSpecificationValue = new CreateSpecificationValue();
                $createSpecificationValue->value = $value;
                $createSpecificationValue->specification = $createSpecification->getSpecificationEntity();
                $createSpecificationValue->meta = new Meta($value, false, $value, false, $value, false, Uri::getUrl($value), false, null, SEOFollow::none(), SEOIndex::none());
                $createSpecificationValue->sequence = $sequence++;
                $this->commandBus->handle($createSpecificationValue);

                // Save reference for other fixtures
                $referenceKey = 'specification_'.strtolower($title).'_'.strtolower($value);
                $this->addReference(md5($referenceKey), $createSpecificationValue->getSpecificationValueEntity());
            }
        }
    }

    private function createProducts(MediaFolder $defaultFolder): void
    {
        $products = $this->readCsv(__DIR__.'/data/products.csv');

        foreach ($products as $product) {
            $createProduct = new CreateProduct();
            $createProduct->title = $product['title'];
            $createProduct->sku = $product['sku'];
            $createProduct->weight = $product['weight'];
            $createProduct->price = $product['price'];
            $createProduct->stock = $product['stock'];
            $createProduct->summary = $product['summary'];
            $createProduct->text = $product['text'];
            $createProduct->meta = new Meta($product['title'], false, $product['title'], false, $product['title'], false, Uri::getUrl($product['title']), false, null, SEOFollow::none(), SEOIndex::none());
            $createProduct->category = $this->getReference(md5('category_'.strtolower($product['category'])));
            $createProduct->brand = $this->getReference(md5('brand_'.strtolower($product['brand'])));
            $createProduct->vat = $this->getReference(md5('vat_'.strtolower($product['vat'])));
            $createProduct->stock_status = $this->getReference(md5('stock_status_available'));

            if (!empty($product['offer'])) {
                $productSpecial = new ProductSpecial();
                $productSpecial->setPrice($product['offer']);
                $productSpecial->setStartDate(new DateTime());
                $createProduct->addSpecial($productSpecial);
            }

            // Add multiple images via the Media module
            $mediaItemIds = [];
            foreach (explode(',', $product['images']) as $imageSrc) {
                $path = $this->fakeUploadImage(
                    __DIR__."/assets/$imageSrc",
                    PATH_WWW.'/src/Frontend/Files/MediaLibrary/00/',
                )->getRealPath();
                $createMediaItem = new CreateMediaItemFromLocalStorageType(
                    $path,
                    $defaultFolder
                );
                $this->commandBus->handle($createMediaItem);
                $mediaItemIds[] = $createMediaItem->getMediaItem()->getId();
            }
            $saveMediaGroup = new SaveMediaGroup($createProduct->images, $mediaItemIds);
            $this->commandBus->handle($saveMediaGroup);

            // Create the product
            $this->commandBus->handle($createProduct);

            // Add product options and specs
            $this->createProductOptionsAndSpecs($createProduct->getProductEntity(), $product['options'] ?? '');
        }
    }

    private function createProductOptionsAndSpecs(Product $product, string $optionsString): void
    {
        if (empty($optionsString)) {
            return;
        }

        $options = json_decode($optionsString, true, 512, JSON_THROW_ON_ERROR);
        foreach ($options as $optionName => $optionValues) {
            if (!$this->hasReference(md5("product_option_$optionName"))) {
                $createProductOption = new CreateProductOption();
                $createProductOption->title = $optionName;
                $createProductOption->required = true;
                $createProductOption->product = $product;
                $createProductOption->type = 1;
                $createProductOption->custom_value_allowed = false;
                $this->commandBus->handle($createProductOption);

                // Save reference for other fixtures
                $referenceKey = 'product_option_'.strtolower($createProductOption->title);
                $this->addReference(md5($referenceKey), $createProductOption->getProductOptionEntity());
            }

            /** @var ProductOption $productOption */
            $productOption = $this->getReference(md5("product_option_$optionName"));

            foreach ($optionValues as $value) {
                $createProductOptionValue = new CreateProductOptionValue();
                $createProductOptionValue->title = $value;
                $createProductOptionValue->price = $product->getPrice();
                $createProductOptionValue->vat = $product->getVat();
                $createProductOptionValue->productOption = $productOption;
                $this->commandBus->handle($createProductOptionValue);

                // Also add the option as a specification
                $specificationReferenceKey = 'specification_'.strtolower($optionName).'_'.strtolower($value);
                if ($this->hasReference(md5($specificationReferenceKey))) {
                    $updateProduct = new UpdateProduct($product);
                    $updateProduct->addSpecificationValue($this->getReference(md5($specificationReferenceKey)));
                    $this->commandBus->handle($updateProduct);
                }
            }
        }
    }
}

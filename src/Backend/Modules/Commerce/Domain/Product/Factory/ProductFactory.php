<?php

namespace Backend\Modules\Commerce\Domain\Product\Factory;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Category\Category;
use Backend\Modules\Commerce\Domain\Category\CategoryDataTransferObject;
use Backend\Modules\Commerce\Domain\Category\Image;
use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\Product\ProductDataTransferObject;
use Backend\Modules\Commerce\Domain\Product\ProductRepository;
use Backend\Modules\Commerce\Domain\ProductSpecial\ProductSpecial;
use Backend\Modules\Commerce\Domain\StockStatus\StockStatus;
use Backend\Modules\Commerce\Domain\StockStatus\StockStatusDataTransferObject;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\Commerce\Domain\Vat\VatDataTransferObject;
use Common\Doctrine\Entity\Meta;
use Common\Doctrine\ValueObject\SEOFollow;
use Common\Doctrine\ValueObject\SEOIndex;
use Common\Uri;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Money\Money;
use Symfony\Component\Form\Extension\Core\DataTransformer\MoneyToLocalizedStringTransformer;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Product>
 *
 * @method static Product|Proxy createOne(array $attributes = [])
 * @method static Product[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Product|Proxy find(object|array|mixed $criteria)
 * @method static Product|Proxy findOrCreate(array $attributes)
 * @method static Product|Proxy first(string $sortedField = 'id')
 * @method static Product|Proxy last(string $sortedField = 'id')
 * @method static Product|Proxy random(array $attributes = [])
 * @method static Product|Proxy randomOrCreate(array $attributes = []))
 * @method static Product[]|Proxy[] all()
 * @method static Product[]|Proxy[] findBy(array $attributes)
 * @method static Product[]|Proxy[] randomSet(int $number, array $attributes = []))
 * @method static Product[]|Proxy[] randomRange(int $min, int $max, array $attributes = []))
 * @method static ProductRepository|RepositoryProxy repository()
 * @method Product|Proxy create(array|callable $attributes = [])
 */
final class ProductFactory extends ModelFactory
{
    /**
     * Our entities use private properties, no setters and a private constructor. Therefore, we have to use a DTO
     * object to instantiate our Entity.
     */
    protected function initialize(): self
    {
        return $this
            ->instantiateWith(function (array $attributes) {
                $dto = new ProductDataTransferObject(null, Locale::fromString('en'));
                $dto->meta = $attributes['meta'];
                $dto->category = $attributes['category'];
                $dto->brand = $attributes['brand'];
                $dto->vat = $attributes['vat'];
                $dto->stock_status = $attributes['stock_status'];
                $dto->hidden = $attributes['hidden'];
                $dto->type = $attributes['type'];
                $dto->title = $attributes['title'];
                $dto->weight = $attributes['weight'];
                $dto->price = $attributes['price'];
                $dto->stock = $attributes['stock'];
                $dto->sku = $attributes['sku'];
                $dto->ean13 = $attributes['ean13'];
                $dto->isbn = $attributes['isbn'];
                $dto->summary = $attributes['summary'];
                $dto->text = $attributes['text'];
                $dto->specials = $attributes['specials'];

                return Product::fromDataTransferObject($dto);
            });
    }

    protected function getDefaults(): array
    {
        $title = self::faker()->unique()->title;

        // Create a default StockStatus
        $stockStatus = new StockStatusDataTransferObject();
        $stockStatus->title = 'Available';
        $stockStatus->locale = Locale::fromString('en');

        // Create a random category
        $category = new CategoryDataTransferObject();
        $category->title = self::faker()->title;
        $category->locale = Locale::fromString('en');
        $category->sequence = 1;
        $category->extraId = 1;
        $category->image = Image::fromString('');
        $category->meta = new Meta($title, false, $title, false, $title, false, Uri::getUrl($title), false, null, false, null, SEOFollow::none(), SEOIndex::none());

        return [
            'meta' => new Meta($title, false, $title, false, $title, false, Uri::getUrl($title), false, null, false, null, SEOFollow::none(), SEOIndex::none()),
            'category' => Category::fromDataTransferObject($category),
            'brand' => null,
            'vat' => null,
            'stock_status' => StockStatus::fromDataTransferObject($stockStatus),
            'hidden' => false,
            'type' => Product::TYPE_DEFAULT,
            'title' => $title,
            'weight' => self::faker()->randomFloat(null, 0.5, 200),
            'price' => Money::EUR((string) self::faker()->randomFloat(2, 1, 1000) * 100),
            'stock' => self::faker()->randomNumber(),
            'sku' => (string) self::faker()->randomNumber(),
            'ean13' => self::faker()->ean13,
            'isbn' => self::faker()->isbn13,
            'summary' => self::faker()->sentence(),
            'text' => self::faker()->paragraph(),
            'specials' => new ArrayCollection(),
        ];
    }

    protected static function getClass(): string
    {
        return Product::class;
    }

    public function withPrice(string $price): self
    {
        $moneyTransformer = new MoneyToLocalizedStringTransformer(2, true, null, 100);

        return $this->addState(['price' => Money::EUR($moneyTransformer->reverseTransform($price))]);
    }

    public function withVat(float $percentage): self
    {
        $vatDTO = new VatDataTransferObject();
        $vatDTO->title = "$percentage %";
        $vatDTO->percentage = $percentage;
        $vatDTO->sequence = 1;
        $vatDTO->locale = Locale::fromString('en');

        return $this->addState(['vat' => Vat::fromDataTransferObject($vatDTO)]);
    }

    public function withNewSpecial(
        string $newPrice,
        DateTimeInterface $startDate,
        DateTimeInterface $endDate = null
    ): self {
        $moneyTransformer = new MoneyToLocalizedStringTransformer(2, true, null, 100);

        $specials = new ArrayCollection();
        $special = new ProductSpecial();
        $special->setPrice(Money::EUR($moneyTransformer->reverseTransform($newPrice)));
        $special->setStartDate($startDate);
        $special->setEndDate($endDate);
        $specials->add($special);

        return $this->addState(['specials' => $specials]);
    }
}

<?php

namespace Backend\Modules\CommerceCashOnDelivery\Domain\CashOnDelivery\Factory;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Category\Category;
use Backend\Modules\Commerce\Domain\Category\CategoryDataTransferObject;
use Backend\Modules\Commerce\Domain\Category\Image;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethod;
use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\Product\ProductDataTransferObject;
use Backend\Modules\Commerce\Domain\ProductSpecial\ProductSpecial;
use Backend\Modules\Commerce\Domain\StockStatus\StockStatus;
use Backend\Modules\Commerce\Domain\StockStatus\StockStatusDataTransferObject;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\Commerce\Domain\Vat\VatDataTransferObject;
use Backend\Modules\CommerceCashOnDelivery\Domain\CashOnDelivery\CashOnDeliveryDataTransferObject;
use Common\Doctrine\Entity\Meta;
use Common\Doctrine\ValueObject\SEOFollow;
use Common\Doctrine\ValueObject\SEOIndex;
use Common\Uri;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Money\Money;
use Symfony\Component\Form\Extension\Core\DataTransformer\MoneyToLocalizedStringTransformer;
use Zenstruck\Foundry\ModelFactory;

class CashOnDeliveryFactory extends ModelFactory
{
    /**
     * Our entities use private properties, no setters and a private constructor. Therefore, we have to use a DTO
     * object to instantiate our Entity.
     */
    protected function initialize(): self
    {
        return $this
            ->instantiateWith(function (array $attributes) {
                $dto = new CashOnDeliveryDataTransferObject(null, Locale::fromString('en'));
                $dto->name = $attributes['name'];
                $dto->isEnabled = $attributes['isEnabled'];
                $dto->orderInitId  = $attributes['orderInitId'];

                return PaymentMethod::fromDataTransferObject($dto);
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
}

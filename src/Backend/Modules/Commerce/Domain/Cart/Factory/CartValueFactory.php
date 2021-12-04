<?php

namespace Backend\Modules\Commerce\Domain\Cart\Factory;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Cart\CartValue;
use Backend\Modules\Commerce\Domain\Cart\CartValueRepository;
use Backend\Modules\Commerce\Domain\Category\Category;
use Backend\Modules\Commerce\Domain\Category\CategoryDataTransferObject;
use Backend\Modules\Commerce\Domain\Category\Image;
use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\Cart\CartRepository;
use Backend\Modules\Commerce\Domain\Product\Factory\ProductFactory;
use Backend\Modules\Commerce\Domain\StockStatus\StockStatus;
use Backend\Modules\Commerce\Domain\StockStatus\StockStatusDataTransferObject;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\Commerce\Domain\Vat\VatDataTransferObject;
use Common\Doctrine\Entity\Meta;
use Common\Doctrine\ValueObject\SEOFollow;
use Common\Doctrine\ValueObject\SEOIndex;
use Common\Uri;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Money\Money;
use Symfony\Component\Form\Extension\Core\DataTransformer\MoneyToLocalizedStringTransformer;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<CartValue>
 *
 * @method static CartValue[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static CartValueRepository|RepositoryProxy repository()
 * @method CartValue|Proxy create(array|callable $attributes = [])
 *
 * @see: https://symfony.com/index.php/bundles/ZenstruckFoundryBundle/current/index.html
 * @codeCoverageIgnore
 */
final class CartValueFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'product' => ProductFactory::new(),
            'quantity' => self::faker()->numberBetween(1, 10),
            'total' => Money::EUR((string) self::faker()->randomFloat(2, 1, 1000) * 100),
        ];
    }

    protected static function getClass(): string
    {
        return CartValue::class;
    }
}

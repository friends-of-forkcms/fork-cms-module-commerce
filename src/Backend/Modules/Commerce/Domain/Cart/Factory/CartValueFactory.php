<?php

namespace Backend\Modules\Commerce\Domain\Cart\Factory;

use Backend\Modules\Commerce\Domain\Cart\CartValue;
use Backend\Modules\Commerce\Domain\Cart\CartValueRepository;
use Backend\Modules\Commerce\Domain\Product\Factory\ProductFactory;
use Money\Money;
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

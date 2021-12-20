<?php

namespace Backend\Modules\Commerce\Domain\Cart\Factory;

use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\Cart\CartRepository;
use Backend\Modules\Commerce\Domain\OrderAddress\Factory\OrderAddressFactory;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Cart>
 *
 * @method static Cart[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static CartRepository|RepositoryProxy repository()
 * @method Cart|Proxy create(array|callable $attributes = [])
 *
 * @see: https://symfony.com/index.php/bundles/ZenstruckFoundryBundle/current/index.html
 * @codeCoverageIgnore
 */
final class CartFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'shipment_address' => OrderAddressFactory::new(),
            'values' => CartValueFactory::new()->many(self::faker()->numberBetween(1, 5)),
            'ip' => self::faker()->ipv4(),
            'session_id' => self::faker()->uuid(),
        ];
    }

    protected static function getClass(): string
    {
        return Cart::class;
    }
}

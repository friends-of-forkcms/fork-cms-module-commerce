<?php

namespace Backend\Modules\Commerce\Domain\ShipmentMethod\Factory;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\ShipmentMethod\Command\UpdateShipmentMethod;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethod;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethodRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<ShipmentMethod>
 *
 * @method static ShipmentMethod[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static ShipmentMethodRepository|RepositoryProxy repository()
 * @method ShipmentMethod|Proxy create(array|callable $attributes = [])
 *
 * @see: https://symfony.com/index.php/bundles/ZenstruckFoundryBundle/current/index.html
 * @codeCoverageIgnore
 */
final class ShipmentMethodFactory extends ModelFactory
{
    /**
     * Our entities use private properties, no setters and a private constructor. Therefore, we have to use a DTO
     * object to instantiate our Entity.
     */
    protected function initialize(): self
    {
        return $this
            ->instantiateWith(function (array $attributes) {
                $dto = new UpdateShipmentMethod(null, Locale::fromString($attributes['locale']));
                $dto->name = $attributes['name'];
                $dto->module = $attributes['module'];
                $dto->isEnabled = $attributes['isEnabled'];

                return ShipmentMethod::fromDataTransferObject($dto);
            });
    }

    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->name(),
            'module' => self::faker()->name(),
            'isEnabled' => self::faker()->boolean(),
            'locale' => 'en',
        ];
    }

    protected static function getClass(): string
    {
        return ShipmentMethod::class;
    }
}

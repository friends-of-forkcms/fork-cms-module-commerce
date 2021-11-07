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
 * @method static ShipmentMethod|Proxy createOne(array $attributes = [])
 * @method static ShipmentMethod[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static ShipmentMethod|Proxy find(object|array|mixed $criteria)
 * @method static ShipmentMethod|Proxy findOrCreate(array $attributes)
 * @method static ShipmentMethod|Proxy first(string $sortedField = 'id')
 * @method static ShipmentMethod|Proxy last(string $sortedField = 'id')
 * @method static ShipmentMethod|Proxy random(array $attributes = [])
 * @method static ShipmentMethod|Proxy randomOrCreate(array $attributes = []))
 * @method static ShipmentMethod[]|Proxy[] all()
 * @method static ShipmentMethod[]|Proxy[] findBy(array $attributes)
 * @method static ShipmentMethod[]|Proxy[] randomSet(int $number, array $attributes = []))
 * @method static ShipmentMethod[]|Proxy[] randomRange(int $min, int $max, array $attributes = []))
 * @method static ShipmentMethodRepository|RepositoryProxy repository()
 * @method ShipmentMethod|Proxy create(array|callable $attributes = [])
 *
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

<?php

namespace Backend\Modules\CommercePickup\Domain\Pickup\Factory;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethod;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethodRepository;
use Backend\Modules\CommercePickup\Domain\Pickup\PickupShipmentMethodDataTransferObject;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<OrderStatus>
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
final class PickupFactory extends ModelFactory
{
    /**
     * Our entities use private properties, no setters and a private constructor. Therefore, we have to use a DTO
     * object to instantiate our Entity.
     */
    protected function initialize(): self
    {
        return $this
            ->instantiateWith(function (array $attributes) {
                $dto = new PickupShipmentMethodDataTransferObject(null, Locale::fromString('en'));
                $dto->name = $attributes['name'];
                $dto->isEnabled = $attributes['isEnabled'];
                $dto->vatId = $attributes['vatId'];

                return ShipmentMethod::fromDataTransferObject($dto);
            });
    }

    protected function getDefaults(): array
    {
        return [
            'name' => 'Pickup in the store',
            'isEnabled' => true,
            'vatId' => "",
        ];
    }

    protected static function getClass(): string
    {
        return ShipmentMethod::class;
    }

    public function isEnabled(): self
    {
        return $this->addState(['isEnabled' => true]);
    }

    public function isDisabled(): self
    {
        return $this->addState(['isEnabled' => false]);
    }

    public function withOrderInitId(int $orderInitId): self
    {
        return $this->addState(['orderInitId' => (string) $orderInitId]);
    }
}

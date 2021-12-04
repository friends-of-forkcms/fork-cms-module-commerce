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
 * @extends ModelFactory<ShipmentMethod>
 *
 * @method static ShipmentMethod[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static ShipmentMethodRepository|RepositoryProxy repository()
 * @method ShipmentMethod|Proxy create(array|callable $attributes = [])
 *
 * @see: https://symfony.com/index.php/bundles/ZenstruckFoundryBundle/current/index.html
 * @codeCoverageIgnore
 */
final class PickupShipmentMethodFactory extends ModelFactory
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

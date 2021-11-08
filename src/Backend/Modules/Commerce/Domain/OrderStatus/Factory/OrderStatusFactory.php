<?php

namespace Backend\Modules\Commerce\Domain\OrderStatus\Factory;

use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatusDataTransferObject;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatusRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<OrderStatus>
 *
 * @method static OrderStatus|Proxy createOne(array $attributes = [])
 * @method static OrderStatus[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static OrderStatus|Proxy find(object|array|mixed $criteria)
 * @method static OrderStatus|Proxy findOrCreate(array $attributes)
 * @method static OrderStatus|Proxy first(string $sortedField = 'id')
 * @method static OrderStatus|Proxy last(string $sortedField = 'id')
 * @method static OrderStatus|Proxy random(array $attributes = [])
 * @method static OrderStatus|Proxy randomOrCreate(array $attributes = []))
 * @method static OrderStatus[]|Proxy[] all()
 * @method static OrderStatus[]|Proxy[] findBy(array $attributes)
 * @method static OrderStatus[]|Proxy[] randomSet(int $number, array $attributes = []))
 * @method static OrderStatus[]|Proxy[] randomRange(int $min, int $max, array $attributes = []))
 * @method static OrderStatusRepository|RepositoryProxy repository()
 * @method OrderStatus|Proxy create(array|callable $attributes = [])
 *
 * @codeCoverageIgnore
 */
final class OrderStatusFactory extends ModelFactory
{
    /**
     * Our entities use private properties, no setters and a private constructor. Therefore, we have to use a DTO
     * object to instantiate our Entity.
     */
    protected function initialize(): self
    {
        return $this
            ->instantiateWith(function (array $attributes) {
                $dto = new OrderStatusDataTransferObject();
                $dto->title = $attributes['title'];
                $dto->color = $attributes['color'];

                return OrderStatus::fromDataTransferObject($dto);
            });
    }

    protected function getDefaults(): array
    {
        return [
            'title' => self::faker()->title(),
            'color' => self::faker()->hexColor(),
        ];
    }

    protected static function getClass(): string
    {
        return OrderStatus::class;
    }
}

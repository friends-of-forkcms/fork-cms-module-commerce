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
 * @method static OrderStatus[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static OrderStatusRepository|RepositoryProxy repository()
 * @method OrderStatus|Proxy create(array|callable $attributes = [])
 *
 * @see: https://symfony.com/index.php/bundles/ZenstruckFoundryBundle/current/index.html
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

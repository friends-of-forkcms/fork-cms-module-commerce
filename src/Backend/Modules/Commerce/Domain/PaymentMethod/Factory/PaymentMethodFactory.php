<?php

namespace Backend\Modules\Commerce\Domain\PaymentMethod\Factory;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\PaymentMethod\Command\UpdatePaymentMethod;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethod;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethodRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<PaymentMethod>
 *
 * @method static PaymentMethod[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static PaymentMethodRepository|RepositoryProxy repository()
 * @method PaymentMethod|Proxy create(array|callable $attributes = [])
 *
 * @see: https://symfony.com/index.php/bundles/ZenstruckFoundryBundle/current/index.html
 * @codeCoverageIgnore
 */
final class PaymentMethodFactory extends ModelFactory
{
    /**
     * Our entities use private properties, no setters and a private constructor. Therefore, we have to use a DTO
     * object to instantiate our Entity.
     */
    protected function initialize(): self
    {
        return $this
            ->instantiateWith(function (array $attributes) {
                $dto = new UpdatePaymentMethod(null, Locale::fromString($attributes['locale']));
                $dto->name = $attributes['name'];
                $dto->module = $attributes['module'];
                $dto->isEnabled = $attributes['isEnabled'];

                return PaymentMethod::fromDataTransferObject($dto);
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
        return PaymentMethod::class;
    }
}

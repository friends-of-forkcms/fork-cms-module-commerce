<?php

namespace Backend\Modules\CommerceCashOnDelivery\Domain\CashOnDelivery\Factory;

use Backend\Modules\Commerce\Domain\PaymentMethod\Factory\PaymentMethodFactory;
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
final class CashOnDeliveryPaymentMethodFactory extends PaymentMethodFactory
{
    protected function getDefaults(): array
    {
        return [
            'name' => 'Cash on delivery',
            'module' => 'CommerceCashOnDelivery',
            'isEnabled' => true,
            'locale' => 'en',
        ];
    }
}

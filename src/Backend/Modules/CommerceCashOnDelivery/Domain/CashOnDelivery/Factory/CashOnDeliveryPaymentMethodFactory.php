<?php

namespace Backend\Modules\CommerceCashOnDelivery\Domain\CashOnDelivery\Factory;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethod;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethodRepository;
use Backend\Modules\CommerceCashOnDelivery\Domain\CashOnDelivery\CashOnDeliveryPaymentMethodDataTransferObject;
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
final class CashOnDeliveryPaymentMethodFactory extends ModelFactory
{
    /**
     * Our entities use private properties, no setters and a private constructor. Therefore, we have to use a DTO
     * object to instantiate our Entity.
     */
    protected function initialize(): self
    {
        return $this
            ->instantiateWith(function (array $attributes) {
                $dto = new CashOnDeliveryPaymentMethodDataTransferObject(null, Locale::fromString('en'));
                $dto->name = $attributes['name'];
                $dto->isEnabled = $attributes['isEnabled'];
                $dto->orderInitId = $attributes['orderInitId'];

                return PaymentMethod::fromDataTransferObject($dto);
            });
    }

    protected function getDefaults(): array
    {
        return [
            'name' => 'Cash on Delivery',
            'isEnabled' => true,
            'orderInitId' => "",
        ];
    }

    protected static function getClass(): string
    {
        return PaymentMethod::class;
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

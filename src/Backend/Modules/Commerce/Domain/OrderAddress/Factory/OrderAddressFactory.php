<?php

namespace Backend\Modules\Commerce\Domain\OrderAddress\Factory;

use Backend\Modules\Commerce\Domain\Country\Country;
use Backend\Modules\Commerce\Domain\Country\Factory\CountryFactory;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddressDataTransferObject;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddressRepository;
use DateTime;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<OrderAddress>
 *
 * @method static OrderAddress[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static OrderAddressRepository|RepositoryProxy repository()
 * @method OrderAddress|Proxy create(array|callable $attributes = [])
 *
 * @see: https://symfony.com/index.php/bundles/ZenstruckFoundryBundle/current/index.html
 * @codeCoverageIgnore
 */
final class OrderAddressFactory extends ModelFactory
{
    /**
     * Our entities use private properties, no setters and a private constructor. Therefore, we have to use a DTO
     * object to instantiate our Entity.
     */
    protected function initialize(): self
    {
        return $this
            ->instantiateWith(function (array $attributes) {
                $dto = new OrderAddressDataTransferObject();
                $dto->country = $attributes['country'];
                $dto->first_name = $attributes['first_name'];
                $dto->last_name = $attributes['last_name'];
                $dto->email_address = $attributes['email_address'];
                $dto->phone = $attributes['phone'];
                $dto->street = $attributes['street'];
                $dto->house_number = $attributes['house_number'];
                $dto->city = $attributes['city'];
                $dto->zip_code = $attributes['zip_code'];

                return OrderAddress::fromDataTransferObject($dto);
            });
    }

    protected function getDefaults(): array
    {
        return [
            'country' => CountryFactory::new(),
            'first_name' => self::faker()->firstName(),
            'last_name' => self::faker()->lastName(),
            'email_address' => self::faker()->email(),
            'phone' => self::faker()->phoneNumber(),
            'street' => self::faker()->streetName(),
            'house_number' => self::faker()->buildingNumber(),
            'city' => self::faker()->city(),
            'zip_code' => self::faker()->postcode(),
        ];
    }

    protected static function getClass(): string
    {
        return OrderAddress::class;
    }
}

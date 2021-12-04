<?php

namespace Backend\Modules\Commerce\Domain\Country\Factory;

use Backend\Modules\Commerce\Domain\Country\Country;
use Backend\Modules\Commerce\Domain\Country\CountryDataTransferObject;
use Backend\Modules\Commerce\Domain\Country\CountryRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Country>
 *
 * @method static Country[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static CountryRepository|RepositoryProxy repository()
 * @method Country|Proxy create(array|callable $attributes = [])
 *
 * @see: https://symfony.com/index.php/bundles/ZenstruckFoundryBundle/current/index.html
 * @codeCoverageIgnore
 */
final class CountryFactory extends ModelFactory
{
    /**
     * Our entities use private properties, no setters and a private constructor. Therefore, we have to use a DTO
     * object to instantiate our Entity.
     */
    protected function initialize(): self
    {
        return $this
            ->instantiateWith(function (array $attributes) {
                $dto = new CountryDataTransferObject();
                $dto->name = $attributes['name'];
                $dto->iso = $attributes['iso'];

                return Country::fromDataTransferObject($dto);
            });
    }

    protected function getDefaults(): array
    {
        return [
            'name' => 'Belgium',
            'iso' => 'BE',
        ];
    }

    protected static function getClass(): string
    {
        return Country::class;
    }
}

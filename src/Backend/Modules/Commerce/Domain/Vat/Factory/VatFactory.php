<?php

namespace Backend\Modules\Commerce\Domain\Vat\Factory;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\Commerce\Domain\Vat\VatDataTransferObject;
use Backend\Modules\Commerce\Domain\Vat\VatRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Vat>
 *
 * @method static Vat[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static VatRepository|RepositoryProxy repository()
 * @method Vat|Proxy create(array|callable $attributes = [])
 *
 * @see: https://symfony.com/index.php/bundles/ZenstruckFoundryBundle/current/index.html
 * @codeCoverageIgnore
 */
final class VatFactory extends ModelFactory
{
    /**
     * Our entities use private properties, no setters and a private constructor. Therefore, we have to use a DTO
     * object to instantiate our Entity.
     */
    protected function initialize(): self
    {
        return $this
            ->instantiateWith(function (array $attributes) {
                $dto = new VatDataTransferObject();
                $dto->id = $attributes['id'];
                $dto->title = $attributes['title'];
                $dto->percentage = $attributes['percentage'];
                $dto->locale = $attributes['locale'];
                $dto->sequence = $attributes['sequence'];

                return Vat::fromDataTransferObject($dto);
            });
    }

    protected function getDefaults(): array
    {
        $percentage = 21.0;

        return [
            'id' => self::faker()->numberBetween(1),
            'title' => $percentage . '%',
            'percentage' => $percentage,
            'locale' => Locale::fromString('en'),
            'sequence' => 1,
        ];
    }

    protected static function getClass(): string
    {
        return Vat::class;
    }
}

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
 * @method static Vat|Proxy createOne(array $attributes = [])
 * @method static Vat[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Vat|Proxy find(object|array|mixed $criteria)
 * @method static Vat|Proxy findOrCreate(array $attributes)
 * @method static Vat|Proxy first(string $sortedField = 'id')
 * @method static Vat|Proxy last(string $sortedField = 'id')
 * @method static Vat|Proxy random(array $attributes = [])
 * @method static Vat|Proxy randomOrCreate(array $attributes = []))
 * @method static Vat[]|Proxy[] all()
 * @method static Vat[]|Proxy[] findBy(array $attributes)
 * @method static Vat[]|Proxy[] randomSet(int $number, array $attributes = []))
 * @method static Vat[]|Proxy[] randomRange(int $min, int $max, array $attributes = []))
 * @method static VatRepository|RepositoryProxy repository()
 * @method Vat|Proxy create(array|callable $attributes = [])
 *
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

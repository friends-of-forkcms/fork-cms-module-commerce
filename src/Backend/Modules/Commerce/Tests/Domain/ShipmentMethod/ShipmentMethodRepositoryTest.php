<?php

namespace Backend\Modules\Commerce\Tests\Domain\ShipmentMethod;

use Backend\Core\Language\Locale;
use Backend\Core\Tests\BackendWebTestCase;
use Backend\Modules\Commerce\Domain\ShipmentMethod\Exception\ShipmentMethodNotFound;
use Backend\Modules\Commerce\Domain\ShipmentMethod\Factory\ShipmentMethodFactory;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethod;
use Backend\Modules\Extensions\Engine\Model as BackendExtensionsModel;
use Doctrine\ORM\EntityManager;
use Zenstruck\Foundry\Test\Factories;

class ShipmentMethodRepositoryTest extends BackendWebTestCase
{
    use Factories;

    // Required by Foundry to know when kernel has booted. Can be removed in later Symfony versions.
    protected static bool $booted = false;

    private ?EntityManager $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        static::$booted = true;
        $this->entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();

        // Install the module(s)
        BackendExtensionsModel::installModule('Commerce');
    }

    /** @test */
    public function it_can_find_an_entity_by_id_and_locale(): void
    {
        $shipmentMethod = ShipmentMethodFactory::new()->create([
            'name' => 'My cool shipment method',
            'locale' => 'en',
        ]);
        ShipmentMethodFactory::createMany(5);

        /** @var ShipmentMethod $result */
        $result = $this->entityManager
            ->getRepository(ShipmentMethod::class)
            ->findOneByIdAndLocale($shipmentMethod->getId(), $shipmentMethod->getLocale());

        // Assert
        $this->assertEquals($shipmentMethod->getId(), $result->getId());
        $this->assertEquals($shipmentMethod->getName(), $result->getName());
    }

    /** @test */
    public function it_throws_an_exception_when_entity_cannot_be_found_using_id_and_locale(): void
    {
        $shipmentMethod = ShipmentMethodFactory::new()->create();

        // Expect exception
        $this->expectException(ShipmentMethodNotFound::class);

        $this->entityManager
            ->getRepository(ShipmentMethod::class)
            ->findOneByIdAndLocale(9999, $shipmentMethod->getLocale());
    }

    /** @test */
    public function it_can_find_all_enabled_shipment_method_names(): void
    {
        $enabledShipmentMethod = ShipmentMethodFactory::new()->create(['isEnabled' => true]);
        $disabledShipmentMethod = ShipmentMethodFactory::new()->create(['isEnabled' => false]);

        /** @var string[] $results */
        $results = $this->entityManager
            ->getRepository(ShipmentMethod::class)
            ->findEnabledShipmentMethods(Locale::fromString('en'));

        // Assert
        $this->assertCount(1, $results);
        $this->assertEquals($enabledShipmentMethod->getName(), $results[0]->getName());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Doing this is recommended to avoid memory leaks
        // https://symfony.com/doc/current/testing/database.html#functional-testing-of-a-doctrine-repository
        $this->entityManager->close();
        $this->entityManager = null;
    }
}

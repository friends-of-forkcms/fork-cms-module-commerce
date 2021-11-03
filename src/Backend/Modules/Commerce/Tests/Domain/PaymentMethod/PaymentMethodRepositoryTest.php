<?php

namespace Backend\Modules\Commerce\Tests\Domain\PaymentMethod;

use Backend\Core\Language\Locale;
use Backend\Core\Tests\BackendWebTestCase;
use Backend\Modules\Commerce\Domain\PaymentMethod\Exception\PaymentMethodNotFound;
use Backend\Modules\Commerce\Domain\PaymentMethod\Factory\PaymentMethodFactory;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethod;
use Backend\Modules\Extensions\Engine\Model as BackendExtensionsModel;
use Doctrine\ORM\EntityManager;
use Zenstruck\Foundry\Test\Factories;

class PaymentMethodRepositoryTest extends BackendWebTestCase
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
        $paymentMethod = PaymentMethodFactory::new()->create([
            'name' => 'My cool payment method',
            'locale' => 'en',
        ]);
        PaymentMethodFactory::createMany(5);

        /** @var PaymentMethod $result */
        $result = $this->entityManager
            ->getRepository(PaymentMethod::class)
            ->findOneByIdAndLocale($paymentMethod->getId(), $paymentMethod->getLocale());

        // Assert
        $this->assertEquals($paymentMethod->getId(), $result->getId());
        $this->assertEquals($paymentMethod->getName(), $result->getName());
    }

    /** @test */
    public function it_throws_an_exception_when_entity_cannot_be_found_using_id_and_locale(): void
    {
        $paymentMethod = PaymentMethodFactory::new()->create();

        // Expect exception
        $this->expectException(PaymentMethodNotFound::class);

        $this->entityManager
            ->getRepository(PaymentMethod::class)
            ->findOneByIdAndLocale(9999, $paymentMethod->getLocale());
    }

    /** @test */
    public function it_can_find_all_enabled_payment_method_names(): void
    {
        $enabledPaymentMethod = PaymentMethodFactory::new()->create(['isEnabled' => true]);
        $disabledPaymentMethod = PaymentMethodFactory::new()->create(['isEnabled' => false]);

        /** @var string[] $results */
        $results = $this->entityManager
            ->getRepository(PaymentMethod::class)
            ->findEnabledPaymentMethodNames(Locale::fromString('en'));

        // Assert
        $this->assertCount(1, $results);
        $this->assertEquals($enabledPaymentMethod->getName(), $results[0]);
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

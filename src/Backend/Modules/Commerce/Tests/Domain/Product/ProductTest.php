<?php

namespace Backend\Modules\Commerce\Tests\Domain\Product;

use Backend\Modules\Commerce\Domain\Product\ProductRepository;
use Backend\Modules\Commerce\Factory\ProductFactory;
use Common\ModulesSettings;
use DateTime;
use ForkCMS\App\BaseModel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zenstruck\Foundry\Test\Factories;

class ProductTest extends TestCase
{
    use Factories;

    protected function setUp(): void
    {
        parent::setUp();

        // The ProductDTO reaches out to the container for fork settings and the product repository, so we have to mock this.
        $forkSettings = $this->getMockBuilder(ModulesSettings::class)->disableOriginalConstructor()->getMock();
        $forkSettings->method('get')->with('Core', 'languages', ['en'])->willReturn(['en']);
        $productRepository = $this->getMockBuilder(ProductRepository::class)->disableOriginalConstructor()->getMock();
        $productRepository->method('getNextSequence')->willReturn(1);

        // Let the container return our mocks
        $container = $this->getMockBuilder(ContainerInterface::class)->disableOriginalConstructor()->getMock();
        $container
            ->method('get')
            ->willReturnMap([
                    ['fork.settings', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $forkSettings],
                    ['commerce.repository.product', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $productRepository]
            ]);
        BaseModel::setContainer($container);
    }

    /** @test */
    public function it_can_get_an_active_price_without_vat(): void
    {
        $product = ProductFactory::new()->withPrice(299.99)->withVat(21.00)->create();
        self::assertEquals(299.99, $product->getOldPrice(false));
        self::assertEquals(299.99, $product->getActivePrice(false));
    }

    /** @test */
    public function it_can_get_an_active_price_with_vat_included(): void
    {
        $product = ProductFactory::new()->withPrice(299.99)->withVat(21.00)->create();
        self::assertEquals(362.9879, $product->getOldPrice(true));
        self::assertEquals(362.9879, $product->getActivePrice(true));
    }

    /** @test */
    public function it_can_get_a_discounted_price_without_vat(): void
    {
        $product = ProductFactory::new()
            ->withPrice(299.99)
            ->withVat(21.00)
            ->withNewSpecial('240', (new DateTime())->modify('-1 day'))
            ->create();
        self::assertEquals(299.99, $product->getOldPrice(false));
        self::assertEquals(240, $product->getActivePrice(false));
        self::assertTrue($product->hasActiveSpecialPrice());
    }

    /** @test */
    public function it_can_get_a_discounted_price_with_vat(): void
    {
        $product = ProductFactory::new()
            ->withPrice(299.99)
            ->withVat(21.00)
            ->withNewSpecial('240', (new DateTime())->modify('-1 day'))
            ->create();
        self::assertEquals(362.9879, $product->getOldPrice(true));
        self::assertEquals(290.4, $product->getActivePrice(true));
        self::assertTrue($product->hasActiveSpecialPrice());
    }

    /** @test */
    public function it_should_not_apply_a_special_before_the_start_of_the_special(): void
    {
        $product = ProductFactory::new()
            ->withPrice(299.99)
            ->withVat(21.00)
            ->withNewSpecial('240', (new DateTime())->modify('+2 days'))
            ->create();
        self::assertEquals(299.99, $product->getOldPrice(false));
        self::assertEquals(299.99, $product->getActivePrice(false));
        self::assertFalse($product->hasActiveSpecialPrice());
    }

    /** @test */
    public function it_should_not_apply_a_special_after_the_special_ended(): void
    {
        $product = ProductFactory::new()
            ->withPrice(299.99)
            ->withVat(21.00)
            ->withNewSpecial('240', (new DateTime())->modify('-9 days'), (new DateTime())->modify('-2 days'))
            ->create();
        self::assertEquals(299.99, $product->getOldPrice(false));
        self::assertEquals(299.99, $product->getActivePrice(false));
        self::assertFalse($product->hasActiveSpecialPrice());
    }

    /** @test */
    public function it_should_calculate_the_current_discount_in_percentage(): void
    {
        $productA = ProductFactory::new()
            ->withPrice(234.00)
            ->withVat(21.00)
            ->withNewSpecial('208.00', (new DateTime())->modify('-2 days'))
            ->create();

        $productB = ProductFactory::new()
            ->withPrice(79.00)
            ->withVat(21.00)
            ->withNewSpecial('39.00', (new DateTime())->modify('-2 days'))
            ->create();

        // A product without actual discount
        $productC = ProductFactory::new()
            ->withPrice(100.00)
            ->withVat(21.00)
            ->withNewSpecial('100.00', (new DateTime())->modify('-2 days'))
            ->create();

        // A product with a sneaky temporary price increase
        $productD = ProductFactory::new()
            ->withPrice(100.00)
            ->withVat(21.00)
            ->withNewSpecial('120.00', (new DateTime())->modify('-2 days'))
            ->create();

        self::assertEquals('-11%', $productA->getDiscountPercentage());
        self::assertEquals('-51%', $productB->getDiscountPercentage());
        self::assertEquals('+0%', $productC->getDiscountPercentage());
        self::assertEquals('+20%', $productD->getDiscountPercentage());
    }
}
